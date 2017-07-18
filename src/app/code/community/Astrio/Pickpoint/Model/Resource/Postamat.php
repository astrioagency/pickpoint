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
 * PickPoint postamat resource model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Resource_Postamat extends Astrio_Pickpoint_Model_Resource_AbstractMapping
{
    /**
     * Serializable fields declaration
     *
     * @var array
     */
    protected $_serializableFields = array(
        'metro_list' => array(null, array()),
    );

    /**
     * PickPoint API to DB fields mapping
     *
     * @var array
     */
    protected static $_fieldsMapping = array(
        'Id'             => 'pt_id',         // varchar:20
        'Number'         => 'pt_number',     // char:8
        'Status'         => 'pt_status',     // smallint
        'Name'           => 'pt_name',       // varchar:80
        'TypeTitle'      => 'type_title',    // char:3 (АПТ/ПВЗ)
        'CitiId'         => 'city_id',       // varchar:20
        'CitiOwnerId'    => 'city_owner_id', // varchar:20
        'CitiName'       => 'city_name',     // varchar:50
        'Region'         => 'region',        // varchar:50
        'CountryName'    => 'country',       // varchar:50
        'Address'        => 'address',       // varchar:255
        'BuildingType'   => 'building_type', // varchar:50
        'Street'         => 'street',        // varchar:255
        'House'          => 'house',         // varchar:150
        'PostCode'       => 'post_code',     // varchar:20
        'Latitude'       => 'latitude',      // float (55.682675)
        'Longitude'      => 'longitude',     // float (37.897516)
        'LocationType'   => 'location_type', // smallint
        'AmountTo'       => 'amount_to',     // varchar:100
        'Metro'          => 'metro',         // varchar:100
        'MetroArray'     => 'metro_list',    // text(array)
        'WorkHourly'     => 'work_hourly',   // smallint(boolean)
        'WorkTime'       => 'work_time',     // varchar:255
        'Card'           => 'card',          // smallint(boolean)
        'Cash'           => 'cash',          // smallint(boolean)
        'Fitting'        => 'fitting',       // smallint(boolean)
        'Returning'      => 'returning',     // smallint(boolean)
        'Opening'        => 'opening',       // smallint(boolean)
        'IndoorPlace'    => 'indoor_place',  // varchar:255
        'InDescription'  => 'in_descr',      // text
        'OutDescription' => 'out_descr',     // text
        'MaxSize'        => 'max_size',      // varchar:255
        'MaxWeight'      => 'max_weight',    // varchar:255
        'OwnerId'        => 'owner_id',      // varchar:20
        'OwnerName'      => 'owner_name',    // varchar:100
        'Comment'        => 'comment',       // text
    );

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('astrio_pickpoint/postamat', 'id');
    }

    /**
     * Returns first found postamat code by city name
     *
     * @param string  $city      City name
     * @param integer $websiteId Website ID (OPTIONAL)
     *
     * @return string|boolean
     */
    public function getPostamatCodeByCity($city, $websiteId = null)
    {
        $adapter = $this->_getReadAdapter();

        $field = $adapter->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'city_name'));

        $order = array(
            $this->getMainTable() . '.region ' . Varien_Db_Select::SQL_ASC,
            $this->getMainTable() . '.city_name ' . Varien_Db_Select::SQL_ASC,
            $this->getMainTable() . '.address ' . Varien_Db_Select::SQL_ASC,
        );

        $select = $adapter->select()
            //->from(array('main_table' => $this->getMainTable()), 'pt_number')
            ->from($this->getMainTable(), 'pt_number')
            ->where($field . ' = ?', $city)
            ->order($order)
            ->limit(1);

        if (isset($websiteId)) {
            // Join zone table
            $this->_joinZone($select, (int) $websiteId, '');
        }

        return $adapter->fetchOne($select);
    }

    /**
     * Returns select object for load object data
     *
     * @param string                          $field  Field
     * @param mixed                           $value  Value
     * @param Astrio_Pickpoint_Model_Postamat $object PickPoint postamat model
     *
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getWebsiteId()) {
            // Join zone table to verify that PickPoint postamat has zone
            $this->_joinZone($select, (int) $object->getWebsiteId(), array('current_zone_id' => 'id'));
        }

        return $select;
    }

    /**
     * Joins zone
     *
     * @param Zend_Db_Select $select    Select object
     * @param integer        $websiteId Website ID (OPTIONAL)
     * @param array|string   $cols      The columns to select from the zone table (OPTIONAL)
     *
     * @return void
     */
    protected function _joinZone(Zend_Db_Select $select, $websiteId, $cols = Varien_Db_Select::SQL_WILDCARD)
    {
        $adapter = $this->_getReadAdapter();

        $joinCondExpr = $adapter->quoteInto(
            $adapter->quoteIdentifier('zone.pt_number')
            . ' = ' . $adapter->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'pt_number'))
            . ' ' . Varien_Db_Select::SQL_AND
            . ' ' . $adapter->quoteIdentifier('zone.website_id')
            . ' = ?',
            (int) $websiteId
        );

        $select->join(array('zone' => $this->getTable('astrio_pickpoint/zone')), $joinCondExpr, $cols);
    }

    /**
     * Inserts a table row with specified data
     *
     * @param array $data Column-value pairs
     *
     * @return integer The number of affected rows
     *
     * @throws Zend_Db_Adapter_Exception
     */
    public function mapAndInsert(array $data)
    {
        // Convert metro list field
        $data['MetroArray'] = Mage::helper('astrio_pickpoint')
            ->convertMetroArrayField($data['MetroArray']);

        if (!isset($data['OwnerName'])) {
            $data['OwnerName'] = '';
        }

        return parent::mapAndInsert($data);
    }

    /**
     * Inserts a table multiply rows with specified data
     *
     * @param array $data Column-value pairs
     *
     * @return int The number of affected rows
     *
     * @throws Zend_Db_Exception
     */
    public function mapAndInsertMultiple(array $data)
    {
        foreach ($data as $postamat) {
            $postamat->MetroArray = Mage::helper('astrio_pickpoint')
                ->convertMetroArrayField($postamat->MetroArray);

            if (!isset($postamat->OwnerName)) {
                $postamat->OwnerName = '';
            }
        }

        return parent::mapAndInsertMultiple($data);
    }
}
