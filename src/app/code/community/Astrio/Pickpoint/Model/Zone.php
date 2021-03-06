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
 * PickPoint zone model
 *
 * @method Astrio_Pickpoint_Model_Resource_Zone _getResource()
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Zone extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_pickpoint_zone';

    /**
     * Parameter name in event (in observer: $observer->getEvent()->getZone())
     *
     * @var string
     */
    protected $_eventObject = 'zone';

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('astrio_pickpoint/zone');
    }

    /**
     * Returns delivery time frame
     *
     * @return string
     */
    public function getDeliveryTimeFrame()
    {
        return ($this->getDeliveryMin() == $this->getDeliveryMax())
            ? $this->getDeliveryMin()
            : $this->getDeliveryMin() . '-' . $this->getDeliveryMax();
    }

    /**
     * Maps and sets API data into DB format
     *
     * @param $key
     * @param null $value
     *
     * @return self
     */
    public function mapAndSetData($key, $value = null)
    {
        $mapping = $this->_getResource()->getApiFieldsMapping();

        if (is_array($key)) {
            $key = Mage::helper('astrio_pickpoint')->mapArrayKeys($key, $mapping);
        } else {
            $key = (isset($mapping[$key])) ? $mapping[$key] : $key;
        }

        return $this->setData($key, $value);
    }
}
