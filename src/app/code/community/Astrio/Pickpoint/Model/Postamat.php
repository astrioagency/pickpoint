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
 * PickPoint postamat model
 *
 * @method Astrio_Pickpoint_Model_Resource_Postamat _getResource()
 *
 * @method Astrio_Pickpoint_Model_Resource_Postamat getWebsiteId()
 *
 * @method integer getCurrentZoneId()
 * @method Astrio_Pickpoint_Model_Resource_Postamat setCurrentZoneId()
 *
 * @method string getPtNumber()
 * @method string getRegion()
 * @method string getCityName()
 * @method string getAddress()
 * @method string getPostCode()
 * @method string getCountry()
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Postamat extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'astrio_pickpoint_postamat';

    /**
     * Parameter name in event (in observer: $observer->getEvent()->getPostamat())
     *
     * @var string
     */
    protected $_eventObject = 'postamat';

    /**
     * Current website zone cache
     *
     * @var Astrio_Pickpoint_Model_Zone|null
     */
    protected $_current_zone = null;

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
     * Sets website ID
     *
     * @param integer $value Value (OPTIONAL)
     *
     * @return self
     */
    public function setWebsiteId($value = null)
    {
        $this->setData('website_id', $value);

        return $this;
    }

    /**
     * Returns postamat city and address
     *
     * @return string
     */
    public function getCityAddress()
    {
        return $this->getCityName() . ', ' . $this->getAddress();
    }

    /**
     * Returns postamat full address
     *
     * @return string
     */
    public function getFullAddress()
    {
        return implode(', ', array(
            $this->getPostCode(), $this->getCountry(), $this->getRegion(), $this->getCityName(), $this->getAddress()
        ));
    }

    /**
     * Returns postamat shipping rate code
     *
     * @return string
     */
    public function getShippingRateCode()
    {
        return Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE . '_' . $this->getPtNumber();
    }

    /**
     * Returns postamat location description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getCityAddress() . ', ' . $this->getOutDescr();
    }

    /**
     * Returns current postamat zone model (for website)
     *
     * @return Astrio_Pickpoint_Model_Zone|null
     */
    public function getCurrentZone()
    {
        if (
            !isset($this->_current_zone)
            && $this->getCurrentZoneId()
        ) {
            $this->_current_zone = Mage::getModel('astrio_pickpoint/zone')
                ->load($this->getCurrentZoneId());
        }

        return $this->_current_zone;
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

    /**
     * Loads model data
     *
     * @param string $code PickPoint postamat code (number)
     *
     * @return self
     */
    public function loadByCode($code)
    {
        return $this->load($code, 'pt_number');
    }
}
