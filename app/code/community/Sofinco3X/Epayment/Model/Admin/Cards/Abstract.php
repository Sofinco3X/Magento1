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

abstract class Sofinco3X_Epayment_Model_Admin_Cards_Abstract
{
    public function getConfigPath()
    {
        return 'default/payment/sf3xep_'.$this->getConfigNodeName().'/cards';
    }

    public abstract function getConfigNodeName();

    public function toOptionArray()
    {
        $result = array();
        $configPath = $this->getConfigPath();
        $cards = Mage::getConfig()->getNode($configPath)->asArray();
        if (!empty($cards)) {
            $helper = Mage::helper('sf3xep');
            foreach ($cards as $code => $card) {
                $result[] = array(
                    'label' => $helper->__($card['label']),
                    'value' => $code,
                );
            }
        }

        return $result;
    }
}
