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

abstract class Sofinco3X_Epayment_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract
{
    const CALL_NUMBER = 'sofinco_call_number';
    const TRANSACTION_NUMBER = 'sofinco_transaction_number';
    const PBXACTION_DEFERRED = 'deferred';
    const PBXACTION_IMMEDIATE = 'immediate';
    const PBXACTION_MANUAL = 'manual';

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = false;
    // Fake to avoid calling au authorize ou capture before redirect
    protected $_isInitializeNeeded = true;
    protected $_formBlockType = 'sf3xep/checkout_payment';
    protected $_infoBlockType = 'sf3xep/info';

    /**
     * Sofinco3X specific options
     */
    protected $_3dsAllowed = false;
    protected $_3dsMandatory = false;
    protected $_allowDeferredDebit = false;
    protected $_allowImmediatDebit = true;
    protected $_allowManualDebit = false;
    protected $_allowRefund = false;
    protected $_hasCctypes = false;
    protected $_processingTransaction = null;

    public function __construct()
    {
        parent::__construct();
        // $config = $this->getSofinco3XConfig();
        // if ($config->getSubscription() == Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER2 || $config->getSubscription() == Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER3) {
            // $this->_canRefund = $this->getAllowRefund();
            // $this->_canCapturePartial = ($this->getSofinco3XAction() == Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL);
            // $this->_canRefundInvoicePartial = $this->_canRefund;
        // } else {
            $this->_canRefund = false;
            $this->_canCapturePartial = false;
            $this->_canRefundInvoicePartial = false;
        // }

        $this->_canCapture = true;
    }

    /**
     * Message translator helper
     */
    public function __($message)
    {
        $helper = Mage::helper('sf3xep');
        $args = func_get_args();
        return call_user_func_array(array($helper, '__'), $args);
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $type
     * @param array $data
     * @param type $closed
     * @param array $infos
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addSofinco3XTransaction(Mage_Sales_Model_Order $order, $type, array $data, $closed, array $infos = array())
    {
        $withCapture = $this->getConfigPaymentAction() != Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;

        $payment = $order->getPayment();
        $txnId = $this->_createTransactionId($data);
        if (empty($txnId)) {
            if (!empty($parent)) {
                $txnId = $parent->getAdditionalInformation(Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER);
            } else {
                throw new Exception('Invalid transaction id ' . $txnId);
            }
        }

        $payment->setTransactionId($txnId);
        $payment->setParentTransactionId(null);
        $transaction = $type;
        $transaction = $payment->addTransaction($transaction);
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $data);
        foreach ($infos as $key => $value) {
            $transaction->setAdditionalInformation($key, $value);
        }

        if (!empty($parent)) {
            $transaction->setParentTxnId($parent->getTxnId());
        }

        $transaction->setIsClosed($closed === true);

        $this->_processingTransaction = $transaction;

        return $transaction;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $type
     * @param array $data
     * @param type $closed
     * @param array $infos
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addSofinco3XDirectTransaction(Mage_Sales_Model_Order $order, $type, array $data, $closed, array $infos, Mage_Sales_Model_Order_Payment_Transaction $parent)
    {
        $withCapture = $this->getConfigPaymentAction() != Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;

        $payment = $order->getPayment();
        $txnId = intval($parent->getAdditionalInformation(Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER));
        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $txnId .= '/' . $now->format('dmYHis');
        $payment->setTransactionId($txnId);
        $payment->setParentTransactionId($parent->getTxnId());
        $transaction = $type;
        $transaction = $payment->addTransaction($transaction);
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $data);
        foreach ($infos as $key => $value) {
            $transaction->setAdditionalInformation($key, $value);
        }

        $transaction->setIsClosed($closed === true);

        $this->_processingTransaction = $transaction;

        return $transaction;
    }

    /**
     * Create transaction ID from Sofinco3X data
     */
    protected function _createTransactionId(array $sofincoData)
    {
        $call = (int) (isset($sofincoData['transaction']) ? $sofincoData['transaction'] : $sofincoData['NUMTRANS']);
        return $call;
    }

    public function getSofinco3XTransaction(Varien_Object $payment, $type, $openedOnly = false)
    {
        $order = $payment->getOrder();

        // Find transaction
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($order)
                ->addPaymentIdFilter($payment->getId())
                ->addTxnTypeFilter($type);

        if ($collection->getSize() == 0) {
            return null;
        }

        if ($openedOnly) {
            foreach ($collection as $item) {
                if ((!is_null($item)) && (!is_null($item->getTransactionId())) && (!$item->getIsClosed())) {
                    return $item;
                }
            }

            return null;
        }

        $item = $collection->getFirstItem();
        if (is_null($item) || is_null($item->getTransactionId())) {
            return null;
        }

        // Transaction found
        return $item;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType());
        return $this;
    }

    /**
     * Cancel payment abstract method
     *
     * @param Varien_Object $payment
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $order = $payment->getOrder();
        $order->addStatusHistoryComment('Call to cancel()');
        $order->save();
        return $this;
    }

    /**
     * Capture payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $this->logDebug(sprintf(
            'Order %s: Capturing amount: %0.2f',
            $order->getIncrementId(),
            $amount
        ));

        // Currently processing a transaction ? Use it.
        if (!is_null($this->_processingTransaction)) {
            $txn = $this->_processingTransaction;

            switch ($txn->getTxnType()) {
                // Already captured
                case Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE:
                    $trxData = $txn->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
                    if (!is_array($trxData)) {
                        Mage::throwException('No transaction found.');
                    }

                    $payment->setTransactionId($txn->getTransactionId());
                    $payment->setSkipTransactionCreation(true);
                    return $this;

                case Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH:
                    // Nothing to do
                    break;

                default:
                    Mage::throwException('Unsupported transaction type ' . $txn->getTxnType());
            }
        }

        // Otherwise, find the good transaction
        else {
            // Find capture transaction
            $txn = $this->getSofinco3XTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
            if (!is_null($txn)) {
                // Find Sofinco3X data
                $trxData = $txn->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
                if (!is_array($trxData)) {
                    Mage::throwException('No transaction found.');
                }

                // Already captured
                $payment->setTransactionId($txn->getTransactionId());
                $payment->setSkipTransactionCreation(true);
                return $this;
            }

            // Find authorization transaction
            $txn = $this->getSofinco3XTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, true);
            if (is_null($txn)) {
                Mage::throwException('Payment never authorized.');
            }
        }

        // Call Sofinco3X Direct
        $sofinco = $this->getSofinco3X();
        $this->logDebug(sprintf(
            'Order %s: Capture - Transaction %d Direct capture for %0.2f',
            $order->getIncrementId(),
            $txn->getTransactionId(),
            $amount
        ));
        $data = $sofinco->directCapture($amount, $order, $txn);
        $this->logDebug(sprintf(
            'Order %s: Capture - Response code %s',
            $order->getIncrementId(),
            $data['CODEREPONSE']
        ));

        // Message
        if ($data['CODEREPONSE'] == '00000') {
            $message = 'Payment was captured by Sofinco3X.';
            $close = true;
        } else {
            $message = 'Sofinco3X direct error (' . $data['CODEREPONSE'] . ': ' . $data['COMMENTAIRE'] . ')';
            $close = false;
        }

        $data['status'] = $message;
        $this->logDebug(sprintf(
            'Order %s: Capture - %s',
            $order->getIncrementId(),
            $message
        ));

        // Transaction
        $type = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
        $captureTxn = $this->_addSofinco3XDirectTransaction(
            $order, $type, $data, $close, array(
            Sofinco3X_Epayment_Model_Payment_Abstract::CALL_NUMBER => $data['NUMAPPEL'],
            Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER => $data['NUMTRANS'],
            ), $txn
        );
        $captureTxn->save();
        if ($close) {
            $captureTxn->close();
            $payment->setSfx3xepCapture(serialize($data));
        }

        // Avoid automatic transaction creation
        $payment->setSkipTransactionCreation(true);
        $payment->save();

        // If Sofinco3X returned an error, throw an exception
        if ($data['CODEREPONSE'] != '00000') {
            Mage::throwException($message);
        }

        // Change order state and create history entry
        $status = $this->getConfigPaidStatus();
        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState($state, $status, $this->__($message));
        $order->setIsInProgress(true);
        $order->save();

        return $this;
    }

    /**
     * Checks parameter send by Sofinco3X to IPN.
     * @param Mage_Sales_Model_Order $order Order
     * @param array $params Parsed call parameters
     */
    public function checkIpnParams(Mage_Sales_Model_Order $order, array $params)
    {
        // Check required parameters
        $requiredParams = array('amount', 'transaction', 'error', 'reference', 'sign', 'date', 'time');
        foreach ($requiredParams as $requiredParam) {
            if (!isset($params[$requiredParam])) {
                $message = $this->__('Missing ' . $requiredParam . ' parameter in Sofinco3X call');
                $this->logFatal(sprintf(
                    'Order %s: (IPN) %s',
                    $order->getIncrementId(),
                    $message
                ));
                Mage::throwException($message);
            }
        }
    }

    public function getAllowDeferredDebit()
    {
        return $this->_allowDeferredDebit;
    }

    public function getAllowImmediatDebit()
    {
        return $this->_allowImmediatDebit;
    }

    public function getAllowManualDebit()
    {
        return $this->_allowManualDebit;
    }

    public function getAllowRefund()
    {
        return $this->_allowRefund;
    }

    public function getCards()
    {
        return $this->getConfigData('cards');
    }

    public function getConfigPaymentAction()
    {
        if ($this->getSofinco3XAction() == Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL) {
            return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
        }

        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    public function getConfigAuthorizedStatus()
    {
        return $this->getConfigData('status/authorized');
    }

    public function getConfigPaidStatus()
    {
        return $this->getConfigData('status/paid');
    }

    public function getConfigAutoCaptureStatus()
    {
        return $this->getConfigData('status/auto_capture');
    }

    public function getHasCctypes()
    {
        return $this->_hasCctypes;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('sf3xep/payment/redirect', array('_secure' => true));
    }

    public function getSofinco3XAction()
    {
        $config = $this->getSofinco3XConfig();
        $action = $this->getConfigData('action');
        switch ($action) {
            case Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_DEFERRED:
                if (!$this->getAllowDeferredDebit()) {
                    return Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_IMMEDIATE;
                }
                break;
            case Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_IMMEDIATE:
                if (!$this->getAllowImmediatDebit()) {
                    // Not possible
                    Mage::throwException('Unexpected condition in getSofinco3XAction');
                }
                break;
            case Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL:
                if ((($config->getSubscription() != Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER2) &&
                        ($config->getSubscription() != Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER3)) ||
                        !$this->getAllowManualDebit()) {
                    return Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_IMMEDIATE;
                }
                break;
            default:
                $action = Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_IMMEDIATE;
        }

        return $action;
    }

    /**
     * @return Sofinco3X_Epayment_Model_Config Sofinco3X configuration object
     */
    public function getSofinco3XConfig()
    {
        return Mage::getSingleton('sf3xep/config');
    }

    /**
     * @return Sofinco3X_Epayment_Model_Config Sofinco3X configuration object
     */
    public function getSofinco3X()
    {
        return Mage::getSingleton('sf3xep/sofinco');
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote)) {
            if ($this->getHasCctypes()) {
                $cctypes = $this->getConfigData('cctypes', ($quote ? $quote->getStoreId() : null));
                $cctypes = preg_replace('/NONE,?/', '', $cctypes);
                return !empty($cctypes);
            }

            return true;
        }

        return false;
    }

    /**
     * Check whether 3DS is enabled
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function is3DSEnabled(Mage_Sales_Model_Order $order)
    {
        // If 3DS is mandatory, answer is simple
        if ($this->_3dsMandatory) {
            return true;
        }

        // If 3DS is not allowed, answer is simple
        if (!$this->_3dsAllowed) {
            return false;
        }

        // Otherwise lets see the configuration
        switch ($this->getConfigData('tds_active')) {
            case 'always':
                return true;
            case 'condition':
                // Minimum order total
                $value = $this->getConfigData('tds_min_order_total');
                if (!empty($value)) {
                    $total = round($order->getGrandTotal(), 2);
                    if ($total >= round($value, 2)) {
                        return true;
                    }
                }
                return false;
        }

        // Always off
        return false;
    }

    public function logDebug($message)
    {
        Mage::log($message, Zend_Log::DEBUG, 'sofinco-epayment.log');
    }

    public function logWarning($message)
    {
        Mage::log($message, Zend_Log::WARN, 'sofinco-epayment.log');
    }

    public function logError($message)
    {
        Mage::log($message, Zend_Log::ERR, 'sofinco-epayment.log');
    }

    public function logFatal($message)
    {
        Mage::log($message, Zend_Log::ALERT, 'sofinco-epayment.log');
    }

    public function makeCapture(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $txn = $this->getSofinco3XTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, true);

        if (empty($txn)) {
            return false;
        }

        if ($txn->getIsClosed()) {
            return false;
        }

        if (!$order->canInvoice()) {
            return false;
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->setTransactionId($txn->getTransactionId());
        $invoice->register();
        $invoice->pay();

        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order);
        $transactionSave->save();

        return true;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        // Check if a different currency has been used
        if ($order->getStoreToOrderRate() != 1) {
            // In this case, convert amount with currency rate
            $amount = $order->getStore()->roundPrice(
                $amount*$order->getStoreToOrderRate()
            );
        }

        // Find capture transaction
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($order)
                ->addPaymentIdFilter($payment->getId())
                ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        if ($collection->getSize() == 0) {
            // If none, error
            Mage::throwException('No payment or capture transaction. Unable to refund.');
        }

        // Transaction found
        $txn = $collection->getFirstItem();

        // Transaction not captured
        if (!$txn->getIsClosed()) {
            Mage::throwException('Payment was not fully captured. Unable to refund.');
        }

        // Call Sofinco3X Direct
        $connector = $this->getSofinco3X();
        $data = $connector->directRefund((float) $amount, $order, $txn);

        // Message
        if ($data['CODEREPONSE'] == '00000') {
            $message = 'Payment was refund by Sofinco3X.';
        } else {
            $message = 'Sofinco3X direct error (' . $data['CODEREPONSE'] . ': ' . $data['COMMENTAIRE'] . ')';
        }

        $data['status'] = $message;
        $this->logDebug(sprintf(
            'Order %s: %s',
            $order->getIncrementId(),
            $message
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
        $order->save();

        return $this;
    }

    /**
     * Delete recurring payments
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function deleteRecurringPayment(Mage_Sales_Model_Order $order)
    {
        $sofinco = $this->getSofinco3X();
        $this->logDebug(sprintf(
            'Order %s: Cancel recurring payment - calling directRecurringDelete',
            $order->getIncrementId()
        ));
        $result = $sofinco->directRecurringDelete($order);

        // Check answer
        if ($result['ACQ'] != 'OK') {
            $message = $this->__('Unable to cancel recurring payment.')."\r\n";
            $message .= $this->__('For more information logon to the PaymentPlatform Back-Office ('.$result['ERREUR'].')');
            $order->addStatusHistoryComment($message);
            $order->save();
            return false;
        }

        $message = $this->__('Recurring payment canceled.');
        $order->addStatusHistoryComment($message);
        $order->save();
        return true;
    }

    /**
     * Validate payment method information object
     *
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        parent::validate();

        if ($this->getHasCctypes()) {
            $info = $this->getInfoInstance();

            $cctype = $info->getCcType();
            if (empty($cctype)) {
                $errorMsg = 'Please select a valid credit card type';
                Mage::throwException($this->__($errorMsg));
            }

            $selected = explode(',', $this->getConfigData('cctypes'));
            if (!in_array($cctype, $selected)) {
                $errorMsg = 'Please select a valid credit card type';
                Mage::throwException($this->__($errorMsg));
            }
        }

        return $this;
    }

    /**
     * When the visitor come back from Sofinco3X using the cancel URL
     */
    public function onPaymentCanceled(Mage_Sales_Model_Order $order)
    {
        $config = $this->getSofinco3XConfig();
        $message = 'Payment was canceled by user on Sofinco3X payment page.';

        // If cron is not active, we cancel the order
        if (!$config->isCronCancelIsActive()) {
            $order->cancel();
            $this->logDebug(sprintf(
                'Order %s: %s',
                $order->getIncrementId(),
                $message
            ));
            $order->save();
        }

        // Add a message
        $status = $order->addStatusHistoryComment($this->__($message));
        $status->save();
    }

    /**
     * When the visitor come back from Sofinco3X using the failure URL
     */
    public function onPaymentFailed(Mage_Sales_Model_Order $order)
    {
        // Message
        $message = 'Customer is back from Sofinco3X payment page.';
        $message = $this->__($message);
        $status = $order->addStatusHistoryComment($message);

        $status->save();
    }

    /**
     * When the visitor is redirected to Sofinco3X
     */
    public function onPaymentRedirect(Mage_Sales_Model_Order $order)
    {
        $info = $this->getInfoInstance();
        $info->setSfx3xepPaymentAction($this->getConfigPaymentAction());
        $info->setSfx3xepSofinco3XAction($this->getSofinco3XAction());
        $info->save();

        // Keep track of this redirection in order history
        $message = 'Redirecting customer to Sofinco3X payment page.';
        $status = $order->addStatusHistoryComment($this->__($message));
        $status->save();

        $this->logDebug(sprintf('Order %s: %s', $order->getIncrementId(), $message));

    }

    /**
     * When the visitor come back from Sofinco3X using the success URL
     */
    public function onPaymentSuccess(Mage_Sales_Model_Order $order, array $data)
    {
        // Message
        $message = 'Customer is back from Sofinco3X payment page.';
        $message = $this->__($message);
        $status = $order->addStatusHistoryComment($message);

        $status->save();
    }

    /**
     * When the IPN is called
     */
    public function onIPNCalled(Mage_Sales_Model_Order $order, array $params)
    {
        try {
            // Check parameters
            $this->checkIpnParams($order, $params);

            // Look for transaction
            $txnId = $this->_createTransactionId($params);
            $txn = Mage::getModel('sales/order_payment_transaction');
            $txn->setOrderPaymentObject($order->getPayment());
            $txn->loadByTxnId($txnId);
            if (!is_null($txn->getTxnId()) && $txn->getIsClosed() == 1) {
                return false;
            }

            // Payment success
            if (in_array($params['error'], array('00000', '00200', '00201', '00300', '00301', '00302', '00303'))) {
                $this->onIPNSuccess($order, $params);
            }

            // Payment refused
            else {
                $this->onIPNFailed($order, $params);
            }

            return true;
        } catch (Exception $e) {
            $this->onIPNError($order, $params, $e);
            throw $e;
        }
    }

    /**
     * When an error has occured in the IPN handler
     */
    public function onIPNError(Mage_Sales_Model_Order $order, array $data, Exception $e = null)
    {
        // Message
        $message = 'An unexpected error have occured while processing Sofinco3X payment (%s).';
        $error = is_null($e) ? 'unknown error' : $e->getMessage();
        $error = $this->__($error);
        $message = $this->__($message, $error);
        $data['status'] = $message;
        $status = $order->addStatusHistoryComment($message);
        $status->save();
        $this->logFatal(sprintf('Order %s: (IPN) %s', $order->getIncrementId(), $message));

        // Transaction
        if (is_null($this->_processingTransaction)) {
            $type = Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
            $this->_addSofinco3XTransaction($order, $type, $data, true);
        } else {
            $this->_processingTransaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $data);
        }

        $order->save();
    }

    /**
     * When the IPN is called to refuse a payment
     */
    public function onIPNFailed(Mage_Sales_Model_Order $order, array $data)
    {
        // Message
        $message = 'Payment was refused by Sofinco3X (%s).';
        $error = $this->getSofinco3X()->toErrorMessage($data['error']);
        $message = $this->__($message, $error);
        $data['status'] = $message;
        $order->addStatusHistoryComment($message);
        $this->logDebug(sprintf(
            'Order %s: (IPN) %s',
            $order->getIncrementId(),
            $message
        ));

        // Transaction
        $type = Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID;
        $this->_addSofinco3XTransaction($order, $type, $data, true);

        $order->save();
    }

    /**
     * When the IPN is called to validate a payment
     */
    public function onIPNSuccess(Mage_Sales_Model_Order $order, array $data)
    {
        $payment = $order->getPayment();

        $withCapture = $this->getConfigPaymentAction() != Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;

        // Message
        if ($withCapture) {
            $message = 'Payment was authorized and captured by Sofinco3X.';
            $status = $this->getConfigPaidStatus();
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $allowedStates = array(
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_PROCESSING,
            );
        } else {
            $message = 'Payment was authorized by Sofinco3X.';
            $status = $this->getConfigAuthorizedStatus();
            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            $allowedStates = array(
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            );
        }

        $data['status'] = $message;

        // Status and message
        $current = $order->getState();

        // Create transaction
        $type = $withCapture ?
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE :
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
        $txn = $this->_addSofinco3XTransaction(
            $order, $type, $data, $withCapture, array(
            Sofinco3X_Epayment_Model_Payment_Abstract::CALL_NUMBER => $data['call'],
            Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER => $data['transaction'],
            )
        );

        // Associate data to payment
        $payment->setSfx3xepAction($this->getSofinco3XAction());
        $payment->setSfx3xepDelay((int) $this->getConfigData('delay'));
        $payment->setSfx3xepAuthorization(serialize($data));
        if ($withCapture) {
            $payment->setSfx3xepCapture(serialize($data));
            $this->_createInvoice($order, $txn);
        }

        // Set status
        if (in_array($current, $allowedStates)) {
            $order->setState($state, $status, $this->__($message));
            $this->logDebug(sprintf(
                'Order %s: Change status to %s',
                $order->getIncrementId(),
                $state
            ));
        } else {
            $order->addStatusHistoryComment($this->__($message));
        }

        $this->logDebug(sprintf(
            'Order %s: %s',
            $order->getIncrementId(),
            $message
        ));

        $order->save();

        // Client notification if needed
        $order->sendNewOrderEmail();
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Payment_Transaction $txn
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _createInvoice($order, $txn)
    {
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->setTransactionId($txn->getTransactionId());
        $invoice->register();
        $invoice->pay();
        $invoice->save();
        $invoice->sendEmail();
        return $invoice;
    }
}
