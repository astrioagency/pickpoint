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

/**
 * Create table 'astrio_pickpoint/city'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_pickpoint/city'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'ID')
    ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '0',
    ), 'City ID')
    ->addColumn('owner_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '0',
    ), 'City owner ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'City name')
    ->addColumn('region_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'City region name')
    // Combined index to get cities list sorted by region_name and name
    ->addIndex($installer->getIdxName('astrio_pickpoint/city', array('region_name', 'name')),
        array('region_name', 'name'))
    ->setComment('PickPoint cities');

$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_pickpoint/postamat'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_pickpoint/postamat'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'ID')
    ->addColumn('pt_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat ID')
    ->addColumn('pt_number', Varien_Db_Ddl_Table::TYPE_CHAR, 8, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat number')
    ->addColumn('pt_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat status')
    ->addColumn('pt_name', Varien_Db_Ddl_Table::TYPE_TEXT, 80, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat name')
    ->addColumn('type_title', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat type')
    ->addColumn('city_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat city ID')
    ->addColumn('city_owner_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat city owner ID')
    ->addColumn('city_name', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat city name')
    ->addColumn('region', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat region')
    ->addColumn('country', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat country')
    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat address')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat street name')
    ->addColumn('house', Varien_Db_Ddl_Table::TYPE_TEXT, 150, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat house number')
    ->addColumn('post_code', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat postal code')
    ->addColumn('latitude', Varien_Db_Ddl_Table::TYPE_NUMERIC, '14,6', array(
        'nullable' => false,
        'default'  => '0',
    ),'PickPoint postamat latitude')
    ->addColumn('longitude', Varien_Db_Ddl_Table::TYPE_NUMERIC, '14,6', array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat longitude')
    ->addColumn('location_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '1',
    ), 'PickPoint postamat location type')
    ->addColumn('metro', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat nearest metro station')
    ->addColumn('metro_list', Varien_Db_Ddl_Table::TYPE_TEXT, 65535, array(
        'nullable' => true,
    ), 'PickPoint postamat nearest metro stations list')
    ->addColumn('work_hourly', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat works day and night')
    ->addColumn('work_time', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat working time')
    ->addColumn('card', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat accepts credit cards')
    ->addColumn('cash', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat accepts cash')
    ->addColumn('fitting', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat has fitting')
    ->addColumn('returning', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat accepts returns')
    ->addColumn('opening', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'PickPoint postamat opening')
    ->addColumn('indoor_place', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat indoor place description')
    ->addColumn('in_descr', Varien_Db_Ddl_Table::TYPE_TEXT, 65535, array(
        'nullable' => true,
    ), 'PickPoint postamat in location description')
    ->addColumn('out_descr', Varien_Db_Ddl_Table::TYPE_TEXT, 65535, array(
        'nullable' => true,
    ), 'PickPoint postamat out location description')
    ->addColumn('max_size', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat max package size description')
    ->addColumn('max_weight', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat max package weight description')
    ->addColumn('owner_id', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat owner ID')
    ->addColumn('owner_name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => false,
        'default'  => '',
    ), 'PickPoint postamat owner name')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, 65535, array(
        'nullable' => true,
    ), 'PickPoint postamat comment')
    ->addIndex($installer->getIdxName('astrio_pickpoint/postamat', array('region', 'city_name', 'address')),
        array('region', 'city_name', 'address'))
    ->addIndex($installer->getIdxName('astrio_pickpoint/postamat', array('city_name')),
        array('city_name'))
    ->addIndex(
        $installer->getIdxName('astrio_pickpoint/postamat', array('pt_number'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('pt_number'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('PickPoint postamats');

$installer->getConnection()->createTable($table);

/**
 * Create table 'astrio_pickpoint/zone'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('astrio_pickpoint/zone'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
    ))
    ->addColumn('origin_city', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'Origin city')
    ->addColumn('dst_city', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        'default'  => '',
    ), 'Destination city')
    ->addColumn('zone', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0',
    ), 'Zone number')
    ->addColumn('shipping_ratio', Varien_Db_Ddl_Table::TYPE_NUMERIC, '4,2', array(
        'nullable' => false,
        'default'  => '1',
    ), 'Shipping cost ratio')
    ->addColumn('pt_number', Varien_Db_Ddl_Table::TYPE_CHAR, 8, array(
        'nullable' => false,
        'default'  => '',
    ), 'Postamat number')
    ->addColumn('delivery_max', Varien_Db_Ddl_Table::TYPE_SMALLINT, 2, array(
        'nullable' => false,
        'default'  => '0',
        'unsigned' => true,
    ), 'Maximum delivery time in days')
    ->addColumn('delivery_min', Varien_Db_Ddl_Table::TYPE_SMALLINT, 2, array(
        'nullable' => false,
        'default'  => '0',
        'unsigned' => true,
    ), 'Minimum delivery time in days')
    ->addIndex(
        $installer->getIdxName('astrio_pickpoint/zone', array('website_id', 'pt_number'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('website_id', 'pt_number'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('astrio_pickpoint/zone', 'website_id', 'core/website', 'website_id'),
        'website_id', $installer->getTable('core/website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('PickPoint zones');

$installer->getConnection()->createTable($table);

$installer->endSetup();
