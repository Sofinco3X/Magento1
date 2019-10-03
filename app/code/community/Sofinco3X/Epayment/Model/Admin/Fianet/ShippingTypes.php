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
class Sofinco3X_Epayment_Model_Admin_Fianet_ShippingTypes extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $helper = Mage::helper('sf3xep');
            $kwixo = Mage::getSingleton('sf3xep/kwixo');
            $this->_options = array(
                array('value' => null, 'label' => ''),
            );
            foreach ($kwixo->getCarrierType() as $value => $label) {
                $this->_options[] = array(
                    'value' => $value,
                    'label' => $helper->__($label),
                );
            }
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
