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
 * @version   0.1.0
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

// Initialization
$installer = $this;
$installer->startSetup();

$catalogEav = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');

$defs = array(
    'sf3xep_action' => array(
        'type' => 'varchar',
    ),
    'sf3xep_delay' => array(
        'type' => 'varchar',
    ),
    'sf3xep_authorization' => array(
        'type' => 'text',
    ),
    'sf3xep_capture' => array(
        'type' => 'text',
    ),
    'sf3xep_first_payment' => array(
        'type' => 'text',
    ),
    'sf3xep_second_payment' => array(
        'type' => 'text',
    ),
    'sf3xep_third_payment' => array(
        'type' => 'text',
    ),
);

$entity = 'order_payment';

foreach ($defs as $name => $def) {
    $installer->addAttribute('order_payment', $name, $def);
}

// Finalization
$installer->endSetup();
