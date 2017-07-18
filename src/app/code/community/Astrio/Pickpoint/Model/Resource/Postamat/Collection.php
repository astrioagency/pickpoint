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
 * PickPoint postamat resource collection model
 *
 * @method Astrio_Pickpoint_Model_Resource_Postamat getResource()
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Resource_Postamat_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Prefix of collection events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_pickpoint_postamat_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'postamats';

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('astrio_pickpoint/postamat');
    }

    /**
     * Joins PickPoint zone table
     *
     * @param integer      $websiteId Website ID (OPTIONAL)
     * @param array|string $cols      The columns to select from the zone table (OPTIONAL)
     *
     * @return self
     */
    public function joinZones($websiteId = null, $cols = Varien_Db_Select::SQL_WILDCARD)
    {
        $adapter = $this->getConnection();

        if (!isset($websiteId)) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }

        $joinCondExpr = $adapter->quoteInto(
            $adapter->quoteIdentifier('zone.pt_number')
                . ' = ' . $adapter->quoteIdentifier('main_table.pt_number')
                . ' ' . Varien_Db_Select::SQL_AND
                . ' ' . $adapter->quoteIdentifier('zone.website_id')
                . ' = ?',
            (int) $websiteId
        );

        return $this->join(array('zone' => 'astrio_pickpoint/zone'), $joinCondExpr, $cols);
    }
}
