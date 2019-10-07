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

class Sofinco3X_Epayment_Adminhtml_Sf3xepController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin');
    }

    /**
     * Fired when an administrator click the total payment on paybox box
     * @return type
     */
    public function invoiceAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $data = $this->getRequest()->getParams();

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $result = $method->makeCapture($order);

        if (!$result) {
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Unable to create an invoice.'));
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

    public function recurringAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $result = $method->deleteRecurringPayment($order);

        if (!$result) {
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Unable to cancel recurring payment.'));
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

    public function refundAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $data = $this->getRequest()->getParams();

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $result = $method->refund($payment, $order->getTotalPaid());

        if (!$result) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to refund order.'));
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Unable to refund order.'));
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Captured transactions have been refunded.'));
            Mage::getSingleton('adminhtml/session')->setCommentText($this->__('Captured transactions have been refunded.'));
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

}
