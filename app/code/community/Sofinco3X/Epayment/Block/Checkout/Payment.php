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

class Sofinco3X_Epayment_Block_Checkout_Payment extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sf3xep/checkout-payment.phtml');
    }

    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        if (!empty($head)) {
            $head->addCss('css/sf3xep/styles.css');
        }

        return parent::_prepareLayout();
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

    public function getMethodLabelAfterHtml()
    {
        $html = array();

        $path = 'payment/'.$this->getMethod()->getCode().'/cards';
        $cards = Mage::getStoreConfig($path, $this->getMethod()->getStore());
		$subscription = Mage::getSingleton('sf3xep/config')->getSubscription();
        foreach ($cards as $card) {
			if($card['card'] == $subscription){
				$url = $this->htmlEscape($this->getSkinUrl($card['image']));
				$alt = $this->htmlEscape($card['label']);
				$html[] = '<img class="sf3xep-payment-logo" src="'.$url.'" alt="'.$alt.'"/>';
			}
        }

        $html = $alt.'<span class="sf3xep-payment-label">'.implode('&nbsp;', $html).'</span>';
        return $html;
    }
}
