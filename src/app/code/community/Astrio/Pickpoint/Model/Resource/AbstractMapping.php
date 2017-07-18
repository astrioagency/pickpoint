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
 * Abstract mapping resource model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
abstract class Astrio_Pickpoint_Model_Resource_AbstractMapping extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     *  Fields mapping list
     *
     * @var array
     */
    protected static $_fieldsMapping = array();

    /**
     * Returns PickPoint to DB fields mapping
     *
     * @return array
     */
    public function getApiFieldsMapping()
    {
        return static::$_fieldsMapping;
    }

    /**
     * Truncate model collection
     *
     * @return self
     */
    public function truncate()
    {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());

        return $this;
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
        return $this->insert(
            Mage::helper('astrio_pickpoint')->mapArrayKeys($data, $this->getApiFieldsMapping())
        );
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
    public function insert(array $data)
    {
        return $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
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
        $_data = array();

        foreach ($data as $k => $v) {
            $_data[] = Mage::helper('astrio_pickpoint')->mapArrayKeys($v, $this->getApiFieldsMapping());
        }

        return $this->insertMultiple($_data);
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
    public function insertMultiple(array $data)
    {
        return $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $data);
    }

    /**
     * Inserts a table row with specified data
     *
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields Update fields pairs or values (OPTIONAL)
     *
     * @return integer
     */
    public function insertOnDuplicate(array $data, array $fields = array())
    {
        return $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }
}
