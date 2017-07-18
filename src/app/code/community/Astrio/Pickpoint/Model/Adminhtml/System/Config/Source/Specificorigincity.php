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
 * Adminhtml system config model: specific origin city selector
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Adminhtml_System_Config_Source_Specificorigincity
{
    /**
     * Options list cache
     *
     * @var null|array
     */
    protected $_options = null;

    /**
     * Additional region sorting order
     *
     * @var array
     */
    protected static $_sortOrder = array(
        'default'            => 100,
        'Московская обл.'    => 10,
        'Ленинградская обл.' => 20,
        'Белоруссия'         => 1000,
        'Казахстан'          => 1100,
        'Украина'            => 1200,
        'Эстония'            => 1300,
    );

    /**
     * Returns options as array
     *
     * @param boolean $isMultiselect Flag: is multi-select enabled or not (OPTIONAL)
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!isset($this->_options)) {

            // Format options list

            $collection = $this->_getCityCollection();

            // Convert to region name => city[] list
            $_cities = array();

            foreach ($collection as $item) {
                /* @var $item Astrio_Pickpoint_Model_City */

                if (!isset($_cities[$item->getRegionName()])) {
                    $_cities[$item->getRegionName()] = array(
                        'name'  => $item->getRegionName(),
                        'value' => array(),
                    );
                }

                $_cities[$item->getRegionName()]['value'][] = array(
                    'name' => $item->getName(),
                );
            }

            // Sort by regions' significance
            usort($_cities, array($this, '_compareRegions'));

            $this->_options = $this->_convertCitiesList($_cities);

            if (!$isMultiselect) {
                array_unshift(
                    $this->_options,
                    array(
                        'label' => Mage::helper('astrio_pickpoint')->__('-- Please select --'),
                        'value' => '',
                        'style' => 'background-color: #dddddd',
                    )
                );
            }

            unset($_cities);
        }

        return $this->_options;
    }

    /**
     * Compares two regions
     *
     * @param array $a Region data
     * @param array $b Region data
     *
     * @return integer
     */
    protected function _compareRegions($a, $b)
    {
        $regionWeightCmp = $this->_compareRegionsSortWeight($a, $b);

        return ($regionWeightCmp !== 0) ? $regionWeightCmp : strcmp($a['name'], $b['name']);
    }

    /**
     * Compares two regions by it's weight
     *
     * @param array $a Region data
     * @param array $b Region data
     *
     * @return integer
     */
    protected function _compareRegionsSortWeight($a, $b)
    {
        $aWeight = $this->_getRegionSortWeight($a['name']);
        $bWeight = $this->_getRegionSortWeight($b['name']);

        return ($aWeight === $bWeight) ? 0 : (($aWeight < $bWeight) ? -1 : 1);
    }

    /**
     * Returns region weight by name
     *
     * @param string $name Region name
     *
     * @return integer
     */
    protected function _getRegionSortWeight($name)
    {
        return (isset(static::$_sortOrder[$name]))
            ? static::$_sortOrder[$name]
            : static::$_sortOrder['default'];
    }

    /**
     * Convert cities list
     *
     * @return array
     */
    protected function _convertCitiesList($cities)
    {
        $list = array();

        if (is_array($cities) && isset($cities['name'])) {

            $list[] = array(
                'label' => $cities['name'],
                'value' => $cities['name'],
            );

        } else if (is_array($cities)) {

            foreach ($cities as $v) {

                $list[] = array(
                    'label' => $v['name'],
                    'value' => (isset($v['value']) && is_array($v['value']))
                        ? $this->_convertCitiesList($v['value'])
                        : $v['name'],
                );
            }
        }

        return $list;
    }

    /**
     * Returns PickPoint city collection model
     *
     * @return Astrio_Pickpoint_Model_Resource_City_Collection
     */
    protected function _getCityCollection()
    {
        /* @var $collection Astrio_Pickpoint_Model_Resource_City_Collection */
        $collection = Mage::getResourceModel('astrio_pickpoint/city_collection')
            ->addFieldToSelect(array('name', 'region_name'))
            ->addFieldToFilter('main_table.name', array('neq' => new Zend_Db_Expr('main_table.region_name')))
            ->addOrder('main_table.region_name', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addOrder('main_table.name', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->getSelect()->group(array('main_table.region_name', 'main_table.name'));

        return $collection;
    }
}
