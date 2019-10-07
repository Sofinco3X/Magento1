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

class Sofinco3X_Epayment_Model_Admin_Payment_Action
{
    public function toOptionArray()
    {
        $immediate = array(
            'value' => 'immediate',
            'label' => Mage::helper('sf3xep')->__('Paid Immediatly')
        );
        $deferred = array(
            'value' => 'deferred',
            'label' => Mage::helper('sf3xep')->__('Defered payment')
        );
        $manual = array(
            'value' => 'manual',
            'label' => Mage::helper('sf3xep')->__('Paid shipping')
        );

        $config = Mage::getSingleton('sf3xep/config');
        if ($config->getSubscription() == Sofinco3X_Epayment_Model_Config::SUBSCRIPTION_OFFER1) {
            $manual['disabled'] = 'disabled';
        }

        return array(
            $immediate['value'] => $immediate,
            $deferred['value'] => $deferred,
            $manual['value'] => $manual,
        );
    }
}