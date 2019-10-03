<?php
/**
 * Sofinco3X Epayment module for Magento
 *
 * Feel free to contact Sofinco3X at support@paybox.com for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@paybox.com so we can mail you a copy immediately.
 *
 *
 * @version   3.0.6
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Sofinco3X_Epayment_Model_Payment_Threetime extends Sofinco3X_Epayment_Model_Payment_Abstract
{
    protected $_code = 'sf3xep_threetime';
    protected $_hasCctypes = true;
    protected $_allowRefund = true;
    protected $_3dsAllowed = true;

    public function checkIpnParams(Mage_Sales_Model_Order $order, array $params)
    {
        if (!isset($params['amount'])) {
            $message = 'Missing amount parameter';
            $this->logFatal(sprintf(
                'Order %d: (IPN) %s',
                $order->getIncrementId(),
                $message
            ));
            Mage::throwException($message);
        }

        if (!isset($params['transaction'])) {
            $message = 'Missing transaction parameter';
            $this->logFatal(sprintf(
                'Order %d: (IPN) %s',
                $order->getIncrementId(),
                $message
            ));
            Mage::throwException($message);
        }
    }

    public function onIPNSuccess(Mage_Sales_Model_Order $order, array $data)
    {

        $cntr = Mage::getSingleton('sf3xep/sofinco');
        $amountScale = $cntr->getCurrencyScale($order);

        $this->logDebug(sprintf(
            'Order %d: Threetime onIPNSuccess for amount %0.2f',
            $order->getIncrementId(),
            round($data['amount'] / $amountScale)
        ));

        $payment = $order->getPayment();

        // Create transaction
        $withCapture = $this->getConfigPaymentAction() != Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
        $type = $withCapture ?
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE :
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
        $txn = $this->_addSofinco3XTransaction(
            $order, $type, $data, false, array(
            Sofinco3X_Epayment_Model_Payment_Abstract::CALL_NUMBER => $data['call'],
            Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER => $data['transaction'],
            )
        );
        if (is_null($payment->getSfx3xepFirstPayment())) {
            $this->logDebug(sprintf(
                'Order %d: First payment',
                $order->getIncrementId()
            ));

            // Message
            $message = 'Payment was authorized and captured by Sofinco3X.';

            // Status
            $status = $this->getConfigPaidStatus();
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $allowedStates = array(
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PROCESSING,
            );
            $current = $order->getState();
            if (in_array($current, $allowedStates)) {
                $order->setState($state, $status, $this->__($message));
            } else {
                $order->addStatusHistoryComment($this->__($message));
            }

            // Additional informations
            $payment->setSfx3xepFirstPayment(serialize($data));
            $payment->setSfx3xepAuthorization(serialize($data));

            $this->logDebug(sprintf(
                'Order %d: %s',
                $order->getIncrementId(),
                $message
            ));

            // Create invoice is needed
            $invoice = $this->_createInvoice($order, $txn);
            // Set status
            if (in_array($current, $allowedStates)) {
                $order->setState($state, $status, $message);
                $this->logDebug(sprintf(
                    'Order %d: Change status to %s',
                    $order->getIncrementId(),
                    $status
                ));
            } else {
                $order->addStatusHistoryComment($message);
            }

            $order->save();
        } elseif (is_null($payment->getSfx3xepSecondPayment())) {
            // Message
            $message = 'Second payment was captured by Sofinco3X.';
            $order->addStatusHistoryComment($this->__($message));

            // Additional informations
            $payment->setSfx3xepSecondPayment(serialize($data));
            $this->logDebug(sprintf(
                'Order %d: %s',
                $order->getIncrementId(),
                $message
            ));
            $transaction = $this->_addSofinco3XDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $data, true, array(), $txn);
            $transaction->save();
            $order->save();
        } elseif (is_null($payment->getSfx3xepThirdPayment())) {
            // Message
            $message = 'Third payment was captured by Sofinco3X.';
            $order->addStatusHistoryComment($this->__($message));

            // Additional informations
            $payment->setSfx3xepThirdPayment(serialize($data));
            $this->logDebug(sprintf(
                'Order %d: %s',
                $order->getIncrementId(),
                $message
            ));

            $transaction = $this->_addSofinco3XDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $data, true, array(), $txn);
            $transaction->save();
            $txn->closeCapture();
            $order->save();
        } else {
            $this->logDebug(sprintf(
                'Order %d: Invalid three-time payment status',
                $order->getIncrementId()
            ));
            Mage::throwException('Invalid three-time payment status');
        }

        $data['status'] = $message;

        // Associate data to payment
        $payment->setSfx3xepAction('three-time');

        $transactionSave = Mage::getModel('core/resource_transaction');
        $transactionSave->addObject($payment);
        if (isset($invoice)) {
            $transactionSave->addObject($invoice);
        }

        $transactionSave->save();

        // Client notification if needed
        $order->sendNewOrderEmail();
    }

    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        // Find capture transaction
        $this->logDebug(sprintf(
            'Order %d: Looking for transactions Payment ID %d Type %s',
            $order->getIncrementId(),
            $payment->getId(),
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE
        ));
        $collection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->setOrderFilter($order->getId())
            ->addPaymentIdFilter($payment->getId())
            ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        if ($collection->getSize() == 0) {
            // If none, error
            Mage::throwException('No payment or capture transaction. Unable to refund.');
        }

        // Call Sofinco3X Direct
        $connector = $this->getSofinco3X();

        foreach ($collection as $txn) {

            // Refund transactions that have been captured
            if (!is_null($txn) && !is_null($txn->getTxnType()) && !$this->txnHasBeenRefunded($order, $payment, $txn->getTxnId())) {

                $cntr = Mage::getSingleton('sf3xep/sofinco');
                $amountScale = $cntr->getCurrencyScale($order);
                $additionalInformation = $txn->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
                $amount = $additionalInformation['amount'];
                $amount = round($amount / $amountScale);
                $this->logDebug(sprintf(
                    'Order %d: Trying to refund transaction ID %d for an amount of %s',
                    $order->getIncrementId(),
                    $txn->getTxnId(),
                    $amount
                ));
                $data = $connector->directRefund((float) $amount, $order, $txn);
        
                // Message
                if ($data['CODEREPONSE'] == '00000') {
                    $message = 'Payment was refund by Sofinco3X.';
                } else {
                    $message = 'Sofinco3X direct error (' . $data['CODEREPONSE'] . ': ' . $data['COMMENTAIRE'] . ')';
                }

                $data['status'] = $message;
                $this->logDebug(sprintf(
                    'Order %d: %s', $order->getIncrementId(), $message
                ));

                // Transaction
                $transaction = $this->_addSofinco3XDirectTransaction($order, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $data, true, array(), $txn);
                $transaction->save();

                // Avoid automatic transaction creation
                $payment->setSkipTransactionCreation(true);

                // If Sofinco3X returned an error, throw an exception
                if ($data['CODEREPONSE'] != '00000') {
                    Mage::throwException($message);
                }

                // Add message to history
                $order->addStatusHistoryComment($this->__($message));

            }
        }

        // And now delete recurring payments
        $this->deleteRecurringPayment($order);

        return $this;
    }

    private function txnHasBeenRefunded($order, Varien_Object $payment, $txnId)
    {
        $this->logDebug(sprintf(
            'Order %d: Looking for a transaction ID %d - Type %s',
            $order->getIncrementId(),
            $txnId,
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
        ));
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($order->getId())
                ->addPaymentIdFilter($payment->getId())
                ->addAttributeToFilter('parent_txn_id', $txnId)
                ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
        if ($collection->getSize() == 0) {
            $this->logDebug(sprintf(
                'Order %d: Transaction %d has NOT already been refunded',
                $order->getIncrementId(),
                $txnId
            ));
            return false;
        } else {
            $this->logDebug(sprintf(
                'Order %d: Transaction %d has already been refunded',
                $order->getIncrementId(),
                $txnId
            ));
            return  true;
        }
    }
}
