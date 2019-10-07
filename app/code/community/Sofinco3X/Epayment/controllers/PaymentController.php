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

class Sofinco3X_Epayment_PaymentController extends Mage_Core_Controller_Front_Action
{
    private function _404()
    {
        $this->_forward('defaultNoRoute');
    }

    private function _loadQuoteFromOrder(Mage_Sales_Model_Order $order)
    {
        $quoteId = $order->getQuoteId();

        // Retrieves quote
        $quote = Mage::getSingleton('sales/quote')->load($quoteId);
        if (empty($quote) || is_null($quote->getId())) {
            $message = 'Not existing quote id associated with the order %d';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $order->getId()));
        }

        return $quote;
    }

    private function _getOrderFromParams(array $params)
    {
        // Retrieves order
        $sofinco = $this->getSofinco3X();
        $order = $sofinco->untokenizeOrder($params['reference']);
        if (is_null($order) || is_null($order->getId())) {
            return null;
        }

        return $order;
    }

    public function cancelAction()
    {
        try {
            $session = $this->getSession();
            $sofinco = $this->getSofinco3X();

            // Retrieves params
            $params = $sofinco->getParams();
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentCanceled($order);

            // Set quote to active
            $this->_loadQuoteFromOrder($order)->setIsActive(true)->save();

            // Cleanup
            $session->unsCurrentSfx3xepOrderId();

            $this->logDebug(sprintf(
                'Order %d: Payment was canceled by user on Sofinco3X payment page.',
                $order->getIncrementId()
            ));

            $message = $this->__('Payment canceled by user');
            $session->addError($message);
        } catch (Exception $e) {
            $this->logDebug(sprintf('cancelAction: %s', $e->getMessage()));
        }

        // Redirect to cart
        $this->_redirect('checkout/cart');
    }

    public function failedAction()
    {
        try {
            $session = $this->getSession();
            $sofinco = $this->getSofinco3X();

            // Retrieves params
            $params = $sofinco->getParams(false, false);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentFailed($order);

            // Set quote to active
            $this->_loadQuoteFromOrder($order)->setIsActive(true)->save();

            // Cleanup
            $session->unsCurrentSfx3xepOrderId();

            $this->logDebug(sprintf(
                'Order %d: Customer is back from Sofinco3X payment page. Payment refused by Sofinco3X (%d).',
                $order->getIncrementId(),
                $params['error']
            ));

            $message = $this->__('Payment refused by Sofinco3X');
            $session->addError($message);
        } catch (Exception $e) {
            $this->logDebug(sprintf('failureAction: %s', $e->getMessage()));
        }

        // Redirect to cart
        $this->_redirect('checkout/cart');
    }

    public function getConfig()
    {
        return Mage::getSingleton('sf3xep/config');
    }

    public function getSofinco3X()
    {
        return Mage::getSingleton('sf3xep/Sofinco3X');
    }

    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function ipnAction()
    {
        try {
            $sofinco = $this->getSofinco3X();

            // Retrieves params
            $params = $sofinco->getParams(true);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // IP not allowed
            // $config = $this->getConfig();
            // $allowedIps = explode(',', $config->getAllowedIps());
            // $currentIp = Mage::helper('core/http')->getRemoteAddr();
            // if (!in_array($currentIp, $allowedIps)) {
            //     $message = $this->__('IPN call from %s not allowed.', $currentIp);
            //     $order->addStatusHistoryComment($message);
            //     $order->save();
            //     $this->logFatal(sprintf('Order %s: (IPN) %s', $order->getIncrementId(), $message));
            //     $message = 'Access denied to %s';
            //     Mage::throwException($this->__($message, $currentIp));
            // }

            // Call payment method
            $method = $order->getPayment()->getMethodInstance();
            $res = $method->onIPNCalled($order, $params);

            if ($res) {
                $this->logDebug(sprintf('Order %d: (IPN) Done.', $order->getIncrementId()));
            } else {
                $this->logDebug(sprintf('Order %d: (IPN) Already done.', $order->getIncrementId()));
            }
        } catch (Exception $e) {
            $this->logFatal(sprintf(
                '(IPN) Exception %s (%s %d).',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            header('Status: 500 Error', true, 500);
        }
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

    public function redirectAction()
    {
        // Retrieves order id
        $session = $this->getSession();
        $orderId = $session->getLastRealOrderId();

        // If none, try previously saved
        if (is_null($orderId)) {
            $orderId = $session->getCurrentSfx3xepOrderId();
        }

        // If none, 404
        if (is_null($orderId)) {
            return $this->_404();
        }

        // Load order
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (is_null($order) || is_null($order->getId())) {
            $session->unsCurrentSfx3xepOrderId();
            return $this->_404();
        }

        // Check order status
        $state = $order->getState();
        if ($state != Mage_Sales_Model_Order::STATE_NEW) {
            $session->unsCurrentSfx3xepOrderId();
            return $this->_404();
        }

        // Save id
        $session->setCurrentSfx3xepOrderId($orderId);

        // Keep track of order for security check
        $orders = $session->getSfx3xepOrders();
        if (is_null($orders)) {
            $orders = array();
        }

        $orders[Mage::helper('core')->encrypt($orderId)] = true;
        $session->setSfx3xepOrders($orders);

        // Payment method
        $order->getPayment()->getMethodInstance()->onPaymentRedirect($order);

        // Render form
        Mage::register('sf3xep/order', $order);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function successAction()
    {
        try {
            $session = $this->getSession();
            $sofinco = $this->getSofinco3X();

            // Retrieves params
            $params = $sofinco->getParams(false, false);
            if ($params === false) {
                return $this->_404();
            }

            // Load order
            $order = $this->_getOrderFromParams($params);
            if (is_null($order) || is_null($order->getId())) {
                return $this->_404();
            }

            // Payment method
            $order->getPayment()->getMethodInstance()->onPaymentSuccess($order, $params);

            // Cleanup
            $session->unsCurrentSfx3xepOrderId();

            $this->logDebug(sprintf(
                'Order %s: Customer is back from Sofinco3X payment page. Payment success.',
                $order->getIncrementId()
            ));

            // Redirect to success page
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Exception $e) {
            $this->logDebug(sprintf('successAction: %s', $e->getMessage()));
        }

        $this->_redirect('checkout/cart');
    }
}
