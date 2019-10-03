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
 * @version   2.0.5
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

// Initialization
$installer = $this;
$installer->startSetup();

$crypt = Mage::helper('sf3xep/encrypt');

$res = Mage::getSingleton('core/resource');
$cnx = $res->getConnection('core-write');
$table = $res->getTableName('core_config_data');

/**
 * Encrypt existing data
 */
// Find raw values
$query = 'select config_id, value from '.$table.' where path in ("sf3xep/merchant/hmackey", "sf3xep/merchant/password")';
$rows = $cnx->fetchAll($query);

// Process each vlaue
foreach ($rows as $row) {
    $id = $row['config_id'];
    $value = $row['value'];

    // Encrypt the value
    $value = $crypt->encrypt($value);

    // And save to the db
    $cnx->update(
        $table,
        array('value' => $value),
        array('config_id = ?' => $id)
    );
}

/**
 * Add default data as encoded if needed
 */

// HMAC Key
$cfg = new Mage_Core_Model_Config();
$query = 'select 1 from '.$table.' where path = "sf3xep/merchant/hmackey" and scope = "default" and scope_id = 0';
$rows = $cnx->fetchAll($query);
if (empty($rows)) {
    $value = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
    $value = $crypt->encrypt($value);
    $cfg->saveConfig('sf3xep/merchant/hmackey', $value);
}

// Password
$cfg = new Mage_Core_Model_Config();
$query = 'select 1 from '.$table.' where path = "sf3xep/merchant/password" and scope = "default" and scope_id = 0';
$rows = $cnx->fetchAll($query);
if (empty($rows)) {
    $value = '1999888I';
    $value = $crypt->encrypt($value);
    $cfg->saveConfig('sf3xep/merchant/password', $value);
}

// Finalization
$installer->endSetup();
