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

class Sofinco3X_Epayment_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sf3xep/info/default.phtml');
    }

    public function getCreditCards()
    {
        $result = array();
        $cards = $this->getMethod()->getCards();
        $selected = explode(',', $this->getMethod()->getConfigData('cctypes'));
        foreach ($cards as $code => $card) {
            if (in_array($code, $selected)) {
                $result[$code] = $card;
            }
        }

        return $result;
    }

    public function getSofinco3XData()
    {
        return unserialize($this->getInfo()->getSfx3xepAuthorization());
    }

    /**
     * @return Sofinco3X_Epayment_Model_Config Sofinco3X configuration object
     */
    public function getSofinco3XConfig()
    {
        return Mage::getSingleton('sf3xep/config');
    }

    public function getCardImageUrl()
    {
        $data = $this->getSofinco3XData();
        $cards = $this->getCreditCards();
        if (isset($cards[$data['cardType']])) {
            $card = $cards[$data['cardType']];
            return $this->getSkinUrl($card['image'], array('_area' => 'frontend'));
        }

        return $this->getSkinUrl('images/sf3xep/'.strtolower($data['cardType']).'.45.png', array('_area' => 'frontend'));
    }

    public function getCardImageLabel()
    {
        $data = $this->getSofinco3XData();
        $cards = $this->getCreditCards();
        if (!isset($cards[$data['cardType']])) {
            return null;
        }

        $card = $cards[$data['cardType']];
        return $card['label'];
    }

    public function isAuthorized()
    {
        $info = $this->getInfo();
        $auth = $info->getSfx3xepAuthorization();
        return !empty($auth);
    }

    public function canCapture()
    {
        $info = $this->getInfo();
        $capture = $info->getSfx3xepCapture();
        $config = $this->getSofinco3XConfig();
        if ($config->getSubscription() == Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER2 || $config->getSubscription() == Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER3) {
            if ($info->getSfx3xepAction() == Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL) {
                $order = $info->getOrder();
                return empty($capture) && $order->canInvoice();
            }
        }

        return false;
    }

    public function canRefund()
    {
        $info = $this->getInfo();
        $method = $info->getOrder()->getPayment()->getMethodInstance();
        if (!$method->getAllowRefund()) {
            return false;
        }

        $config = $this->getSofinco3XConfig();
        if ($config->getSubscription() != Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER2 && $config->getSubscription() != Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER3) {
            return false;
        }

        $order = $info->getOrder();
        $payment = $order->getPayment();
        $collection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->setOrderFilter($order->getId())
            ->addPaymentIdFilter($payment->getId())
            ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);

        // No transactions to refund
        if ($collection->getSize() == 0) {
            return false;
        }

        // Check if at least one transaction can be refund
        foreach ($collection as $txn) {
            if (!is_null($txn) && !is_null($txn->getTxnType()) && !$this->txnHasBeenRefunded($order, $payment, $txn->getTxnId())) {
                return true;
            }
        }

        return false;
    }

    public function getDebitTypeLabel()
    {
        $info = $this->getInfo();
        $action = $info->getSfx3xepAction();
        if (is_null($action) || ($action == 'three-time')) {
            return null;
        }

        $actions = Mage::getSingleton('sf3xep/admin_payment_action')->toOptionArray();
        $result = $actions[$action]['label'];
        if (($info->getSfx3xepAction() == Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_DEFERRED) && (!is_null($info->getSfx3xepDelay()))) {
            $delays = Mage::getSingleton('sf3xep/admin_payment_delays')->toOptionArray();
            $result .= ' (' . $delays[$info->getSfx3xepDelay()]['label'] . ')';
        }

        return $result;
    }

    public function getShowInfoToCustomer()
    {
        $config = $this->getSofinco3XConfig();
        return $config->getShowInfoToCustomer() != 0;
    }

    public function getThreeTimeLabels()
    {
        $info = $this->getInfo();
        $action = $info->getSfx3xepAction();
        if (is_null($action) || ($action != 'three-time')) {
            return null;
        }

        $result = array(
            'first' => $this->__('Not achieved'),
            'second' => $this->__('Not achieved'),
            'third' => $this->__('Not achieved'),
        );

        $data = $info->getSfx3xepFirstPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['first'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        $data = $info->getSfx3xepSecondPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['second'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        $data = $info->getSfx3xepThirdPayment();
        if (!empty($data)) {
            $data = unserialize($data);
            $date = preg_replace('/^([0-9]{2})([0-9]{2})([0-9]{4})$/', '$1/$2/$3', $data['date']);
            $result['third'] = $this->__('%s (%s)', $data['amount'] / 100.0, $date);
        }

        return $result;
    }

    public function getPartialCaptureUrl()
    {
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/sales_order_invoice/start", array(
                    'order_id' => $info->getOrder()->getId(),
            )
        );
    }

    public function getCaptureUrl()
    {
        $data = $this->getSofinco3XData();
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/sf3xep/invoice", array(
                    'order_id' => $info->getOrder()->getId(),
                    'transaction' => $data['transaction'],
            )
        );
    }

    public function getRefundUrl()
    {
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/sf3xep/refund", array(
                    'order_id' => $info->getOrder()->getId(),
            )
        );
    }

    public function getRecurringDeleteUrl()
    {
        $data = $this->getSofinco3XData();
        $info = $this->getInfo();
        return Mage::helper("adminhtml")->getUrl(
            "*/sf3xep/recurring", array(
                    'order_id' => $info->getOrder()->getId(),
            )
        );
    }

    public function threeTimeClosed()
    {
        $info = $this->getInfo();
        $action = $info->getSfx3xepAction();
        if (is_null($action) || ($action != 'three-time')) {
            return true;
        }

        return false;
    }

    private function txnHasBeenRefunded($order, Varien_Object $payment, $txnId)
    {
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($order->getId())
                ->addPaymentIdFilter($payment->getId())
                ->addAttributeToFilter('parent_txn_id', $txnId)
                ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);

        return $collection->getSize() > 0;
    }

}
