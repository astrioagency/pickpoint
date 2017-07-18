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
 * Module default helper
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Postamat checkout session key
     */
    const POSTAMAT_SESSION_KEY = '__astrio_pickpoint_selected_postamat';

    /**
     * Number of entities inserted together
     */
    const IMPORT_BULK_CITIES   = 50;
    const IMPORT_BULK_POSTAMAT = 20;
    const IMPORT_BULK_ZONES    = 50;

    /**
     * Returns number of cities inserted together
     *
     * @return integer
     */
    public function getImportBulkCities()
    {
        return self::IMPORT_BULK_CITIES;
    }

    /**
     * Returns number of postamats inserted together
     *
     * @return integer
     */
    public function getImportBulkPostamat()
    {
        return self::IMPORT_BULK_POSTAMAT;
    }

    /**
     * Returns number of zones inserted together
     *
     * @return integer
     */
    public function getImportBulkZones()
    {
        return self::IMPORT_BULK_ZONES;
    }

    /**
     * Bulk array update
     *
     * @param array   $data     Array
     * @param integer $step     Step items
     * @param mixed   $callback Callback
     *
     * @return void
     */
    public function bulkArrayUpdate(array $data, $step, $callback)
    {
        $update = array();

        foreach ($data as $k => $v) {

            $update[] = $v;

            if ($k % $step == 0) {
                call_user_func_array($callback, array($update));
                $update = array();
            }
        }

        if (!empty($update)) {
            call_user_func_array($callback, array($update));
        }

        unset($update);
    }

    /**
     * Maps array keys by keys mapping
     *
     * @param array   $array      Source array
     * @param array   $mapping    Keys mapping
     * @param boolean $removeKeys Flag: remove keys if they were not found (OPTIONAL)
     *
     * @return array
     */
    public function mapArrayKeys($array, $mapping, $removeKeys = true)
    {
        $result = array();

        foreach ($array as $k => $v) {
            $key = (isset($mapping[$k])) ? $mapping[$k] : $k;

            if (!$removeKeys || isset($mapping[$k])) {
                $result[$key] = $v;
            }
        }

        return $result;
    }

    /**
     * Converts metro array to serealized list
     *
     * @var array $metroList Metro list
     *
     * @return string
     */
    public function convertMetroArrayField($list)
    {
        return (!empty($list) && is_array($list))
            ? serialize($list)
            : '';
    }

    /**
     * Returns PickPoint API model instance
     *
     * @param string $store Store code (OPTIONAL)
     *
     * @return false|Astrio_Pickpoint_Model_Api
     */
    public function getApiInstance($store = null)
    {
        return Mage::getModel('astrio_pickpoint/api', array('store' => $store));
    }

    /**
     * Checks if zone rates list has valid zone identifications and delivery dates
     *
     * @param array $zones Zone rates list
     *
     * @return boolean
     */
    public function checkZoneRatesList($zones)
    {
        $result = true;

        if (!empty($zones) && is_array($zones)) {
            foreach ($zones as $zone) {
                if (!$this->checkZoneRate($zone)) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns "true" if zone rate is valid (has valid zone ID and delivery dates)
     *
     * @param stdClass $zone Zone rate
     *
     * @return boolean
     */
    public function checkZoneRate($zone)
    {
        return (
            isset($zone->Zone)
            && isset($zone->DeliveryMax)
            && isset($zone->DeliveryMin)
        );
    }

    /**
     * Saves selected postamat data to checkout session
     *
     * @param array $value Value (OPTIONAL)
     *
     * @return self
     */
    public function setSessionPostamatData($value = null)
    {
        Mage::getSingleton('checkout/session')->setData(self::POSTAMAT_SESSION_KEY, $value);

        return $this;
    }

    /**
     * Returns selected postamat data
     *
     * @return mixed
     */
    public function getSessionPostamatData()
    {
        return Mage::getSingleton('checkout/session')->getData(self::POSTAMAT_SESSION_KEY);
    }
}
