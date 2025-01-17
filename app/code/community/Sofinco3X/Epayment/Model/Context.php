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

class Sofinco3X_Epayment_Model_Context
{
    private $_order;

    public static function generateToken(Mage_Sales_Model_Order $order)
    {
        $reference = array();
        $reference[] = $order->getRealOrderId();
        $reference[] = $order->getCustomerName();
        $reference = implode(' - ', $reference);
        return $reference;
    }

    public function getOrder()
    {
        return $this->_order;
    }


    /**
     * Reference = order id and customer name
     * The data integrity check is provided by the customer name
     */
    public function getToken()
    {
        return self::generateToken($this->getOrder());
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }

    public function setToken($reference)
    {
        $parts = explode(' - ', $reference, 2);
        if (count($parts) < 2) {
            $message = 'Invalid decrypted reference "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $reference));
        }

        // Retrieves order
        $order = Mage::getSingleton('sales/order')->loadByIncrementId($parts[0]);
        if (empty($order)) {
            $message = 'Not existing order id from decrypted reference "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $reference));
        }

        if (is_null($order->getId())) {
            $message = 'Not existing order id from decrypted reference "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $reference));
        }

        if ($order->getCustomerName() != $parts[1]) {
            $message = 'Consistency error on descrypted reference "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $reference));
        }

        $this->_order = $order;
    }
}
