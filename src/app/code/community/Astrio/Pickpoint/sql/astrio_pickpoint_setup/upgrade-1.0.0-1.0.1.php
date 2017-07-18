<?php
/**
 * Astrio Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@astrio.net so we can send you a copy immediately.
 *
 * @category  Astrio
 * @package   Astrio_Pickpoint
 * @copyright Copyright (c) 2010-2017 Astrio Co. (http://astrio.net)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Install/upgrade script
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
 
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

// Add new 'building_type' column to the 'astrio_pickpoint/postamat' table
$installer->getConnection()
    ->addColumn($installer->getTable('astrio_pickpoint/postamat'), 'building_type', array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 50,
        'nullable' => false,
        'default'  => '',
        'comment'  => 'PickPoint postamat building type',
        'after'    => 'address',
    ));

$installer->endSetup();
