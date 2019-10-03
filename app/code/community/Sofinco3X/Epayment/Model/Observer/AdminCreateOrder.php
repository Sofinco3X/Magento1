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
 * @version   3.0.4
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Sofinco3X_Epayment_Model_Observer_AdminCreateOrder extends Mage_Core_Model_Observer
{
    private static $_oldOrder = null;

    public function onBeforeCreate($observer)
    {
        $event = $observer->getEvent();
        $session = $event->getSession();

        if ($session->getOrder()->getId()) {
            Sofinco3X_Epayment_Model_Observer_AdminCreateOrder::$_oldOrder = $session->getOrder();
        }
    }

    public function onAfterSubmit($observer)
    {
        $oldOrder = Sofinco3X_Epayment_Model_Observer_AdminCreateOrder::$_oldOrder;
        if (!is_null($oldOrder)) {
            $order = $observer->getEvent()->getOrder();
            if (!is_null($order)) {
                $payment = $order->getPayment();
                $oldPayment = $oldOrder->getPayment();

                // Payment information
                $payment->setSfx3xepAction($oldPayment->getSfx3xepAction());
                $payment->setSfx3xepAuthorization($oldPayment->getSfx3xepAuthorization());
                $payment->setSfx3xepCapture($oldPayment->getSfx3xepCapture());
                $payment->setSfx3xepFirstPayment($oldPayment->getSfx3xepFirstPayment());
                $payment->setSfx3xepSecondPayment($oldPayment->getSfx3xepSecondPayment());
                $payment->setSfx3xepSecondThird($oldPayment->getSfx3xepSecondPThird());
                $payment->setSfx3xepDelay($oldPayment->getSfx3xepDelay());
                $payment->setSfx3xepSecondPayment($oldPayment->getSfx3xepSecondPayment());

                // Transactions
                $oldTxns = Mage::getModel('sales/order_payment_transaction')->getCollection();
                $oldTxns->addFilter('payment_id', $oldPayment->getId());
                foreach ($oldTxns as $oldTxn) {
                    $payment->setTransactionId($oldTxn->getTxnId());
                    $payment->setParentTransactionId($oldTxn->getParentTxnId());
                    $txn = $payment->addTransaction($oldTxn->getTxnType());
                    $txn->setParentTxnId($oldTxn->getParentTxnId());
                    $txn->setIsClosed($oldTxn->getIsClosed());
                    $infos = $oldTxn->getAdditionalInformation();
                    foreach ($infos as $key => $value) {
                        $txn->setAdditionalInformation($key, $value);
                    }

                    $txn->setOrderPaymentObject($payment);
                    $txn->setPaymentId($payment->getId());
                    $txn->setOrderId($order->getId());
                    $txn->save();
                }

                $payment->save();
            }
        }
    }
}
