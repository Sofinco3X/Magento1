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
class Sofinco3X_Epayment_Block_Redirect extends Mage_Page_Block_Html
{
    public function getFormFields()
    {
        $order = Mage::registry('sf3xep/order');
        $payment = $order->getPayment()->getMethodInstance();
        $cntr = Mage::getSingleton('sf3xep/Sofinco3X');
        $values = $cntr->buildSystemParams($order, $payment);
        $cntr->logDebug(sprintf('Values: %s', json_encode($values)));
        return $values;
    }

    public function getInputType()
    {
        $config = Mage::getSingleton('sf3xep/config');
        if ($config->isDebug()) {
            return 'text';
        }

        return 'hidden';
    }

    public function getKwixoUrl()
    {
        $sofinco = Mage::getSingleton('sf3xep/Sofinco3X');
        $urls = $sofinco->getConfig()->getKwixoUrls();
        return $sofinco->checkUrls($urls);
    }

    public function getAncvUrl()
    {
        $sofinco = Mage::getSingleton('sf3xep/Sofinco3X');
        $urls = $sofinco->getConfig()->getAncvUrls();
        return $sofinco->checkUrls($urls);
    }

    public function getMobileUrl()
    {
        $sofinco = Mage::getSingleton('sf3xep/Sofinco3X');
        $urls = $sofinco->getConfig()->getMobileUrls();
        return $sofinco->checkUrls($urls);
    }

    public function getSystemUrl()
    {
        $sofinco = Mage::getSingleton('sf3xep/Sofinco3X');
        $urls = $sofinco->getConfig()->getSystemUrls();
        return $sofinco->checkUrls($urls);
    }

    public function getResponsiveUrl()
    {
        $sofinco = Mage::getSingleton('sf3xep/Sofinco3X');
        $urls = $sofinco->getConfig()->getResponsiveUrls();
        return $sofinco->checkUrls($urls);
    }
}
