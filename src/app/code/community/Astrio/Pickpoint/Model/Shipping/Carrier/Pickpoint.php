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
 * PickPoint carrier model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Code of the carrier
     */
    const CODE = 'astriopickpoint';

    /**
     * PickPoint postamat field name
     */
    const REQUEST_FIELD_NAME = 'astrio_pickpoint_postamat_code';

    /**
     * Config parameters
     */
    const CONFIG_PARAM_MIN_PACKAGE_VALUE = 'min_package_value';
    const CONFIG_PARAM_MAX_PACKAGE_VALUE = 'max_package_value';

    /**
     * Origin city types
     */
    const ORIGIN_CITY_USE_SHIPPING = 1;
    const ORIGIN_CITY_USE_SELECTED = 2;

    /**
     * Config zone prefix
     */
    const CONFIG_SHIPPING_RATE_REGION_PREFIX = 'ship_rate_region_';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request model
     *
     * @var Mage_Shipping_Model_Rate_Request|null
     */
    protected $_request = null;

    /**
     * PickPoint API instance
     *
     * @var null
     */
    protected $_apiInstance = null;

    /**
     * Allowed methods list
     *
     * @var array
     */
    protected $_allowedMethods = null;

    /**
     * Available destination (shipping) countries list
     *
     * @var array
     */
    protected static $_availableDestCountries = array(
        'RU'
    );

    /**
     * Returns carrier title
     *
     * @return string
     */
    public function getCarrierTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Returns available destination (shipping) countries list
     *
     * @return array
     */
    public static function getAvailableDestCountries()
    {
        return self::$_availableDestCountries;
    }

    /**
     * Flag: city option required
     *
     * @return boolean
     */
    public function isCityRequired()
    {
        return true;
    }

    /**
     * Returns allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        if (!isset($this->_allowedMethods)) {

            $this->_allowedMethods = array();

            $collection = $this->_getPostamatCollection()
                ->addFieldToSelect(array('pt_number', 'city_name', 'address'));

            foreach ($collection as $postamat) {
                /* @var $postamat Astrio_Pickpoint_Model_Postamat */
                $this->_allowedMethods[$postamat->getPtNumber()] = $postamat->getCityAddress();
            }
        }

        return $this->_allowedMethods;
    }

    /**
     * Processes additional validation to check carrier applicability
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request
     *
     * @return Mage_Shipping_Model_Carrier_Abstract|Mage_Shipping_Model_Rate_Result_Error|boolean
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();

        if ($checkoutSessionQuote && $checkoutSessionQuote->getIsMultiShipping()) {
            // PickPoint is not available for multi-shipping checkout
            return false;
        }

        return $this;
    }

    /**
     * Checks available shipping (destination) countries
     *
     * @param Mage_Shipping_Model_Rate_Request $request Request data
     *
     * @return self|boolean|Mage_Shipping_Model_Rate_Result_Error
     */
    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        if (in_array($request->getDestCountryId(), self::getAvailableDestCountries())) {

            // Check specified countries
            $result = parent::checkAvailableShipCountries($request);

        } else {

            if ($this->getConfigData('showmethod')) {

                /* @var @error Mage_Shipping_Model_Rate_Result_Error */
                $error = Mage::getModel('shipping/rate_result_error');

                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));

                $errorMsg = $this->getConfigData('specificerrmsg');

                if (empty($errorMsg)) {
                    $errorMsg = Mage::helper('shipping')->__('The shipping module is not available for selected delivery country.');
                }

                $error->setErrorMessage($errorMsg);

                $result = $error;

            } else {

                $result = false;
            }
        }

        return $result;
    }

    /**
     * Collects rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request Shipping rate request model
     *
     * @return Mage_Shipping_Model_Rate_Result|boolean
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = false;

        $this->_request = $request;

        if (
            $this->isActive()
            && $this->_checkPackageValue()
        ) {
            // Calculate shipping rates

            $postamat_code = $this->_detectPostamatCode();

            /* @var $postamat Astrio_Pickpoint_Model_Postamat */
            $postamat = Mage::getModel('astrio_pickpoint/postamat')
                ->setWebsiteId($this->_getRequest()->getWebsiteId())
                ->load($postamat_code, 'pt_number');

            if ($postamat->getPtNumber()) {

                /* @var $result Mage_Shipping_Model_Rate_Result */
                $result = Mage::getModel('shipping/rate_result');

                /* @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->getCarrierCode());
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($postamat->getPtNumber());
                $method->setMethodTitle($postamat->getPtName() . ' (' . $postamat->getPtNumber() . ')');
                $method->setMethodDescription($postamat->getCityAddress());

                // Get shipping rate price
                $cost = $this->_getShippingMethodPriceByZone(
                    $postamat->getCurrentZone()->getZone()
                );

                // Add zone shipping ratio
                $cost *= doubleval($postamat->getCurrentZone()->getShippingRatio());

                $method->setCost($cost);
                $method->setPrice($this->getMethodPrice($cost));

                $method->setDeliveryTime($postamat->getCurrentZone()->getDeliveryTimeFrame());

                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Calculates final price considering free shipping
     *
     * @param string $cost   Shipping cost
     * @param string $method Method code (OPTIONAL)
     *
     * @return float|string
     */
    public function getMethodPrice($cost, $method = '')
    {
        return ($this->_isFreeShippingAvailable()) ? '0.00' : $cost;
    }

    /**
     * Returns "true" if free shipping is available
     *
     * @return boolean
     */
    protected function _isFreeShippingAvailable()
    {
        return (
            $this->getConfigFlag('free_shipping_enable')
            && $this->getConfigData('free_shipping_subtotal') <= $this->_request->getBaseSubtotalInclTax()
        );
    }

    /**
     * Returns "true" if package value (subtotal) limitations are meet
     *
     * @return boolean
     */
    protected function _checkPackageValue()
    {
        $packageValue = $this->_getRequest()->getPackageValue();

        $minValue = $this->getConfigData(self::CONFIG_PARAM_MIN_PACKAGE_VALUE);
        $maxValue = $this->getConfigData(self::CONFIG_PARAM_MAX_PACKAGE_VALUE);

        return (
            (empty($minValue) || $packageValue >= doubleval($minValue))
            && (empty($maxValue) || $packageValue <= doubleval($maxValue))
        );
    }

    /**
     * Detects and returns PickPoint postamat code
     *
     * @return string|boolean
     */
    protected function _detectPostamatCode()
    {
        $data = Mage::helper('astrio_pickpoint')->getSessionPostamatData();

        $destCity = (!$this->_getRequest()->getDestCity())
            ? Mage::getModel('directory/region')->load($this->_getRequest()->getDestRegionId())->getName()
            : $this->_getRequest()->getDestCity();

        if (
            !isset($data['code'])
            || empty($data['code'])
            || !isset($data['destCity'])
            || $data['destCity'] != $destCity
        ) {
            // Detect default postamat code by destination city/region
            $data = array(
                'code' => $this->_detectDefaultPostamatCodeByCity($destCity),
            );

            if (empty($data['code'])) {
                // Detect default postamat code by default city (origin city)
                $destCity = $this->_getDefaultCityName();
                $data['code'] = $this->_detectDefaultPostamatCodeByCity($destCity);
            }

            $data['destCity'] = $destCity;

            Mage::helper('astrio_pickpoint')->setSessionPostamatData($data);
        }

        return (empty($data['code'])) ? false : $data['code'];
    }

    /**
     * Detects and returns default PickPoint postamat code by city name and optionally by website ID
     *
     * @param  string  $city      City name
     * @param  integer $websiteId Website ID (OPTIONAL)
     *
     * @return mixed
     */
    protected function _detectDefaultPostamatCodeByCity($city, $websiteId = null)
    {
        $websiteId = (!isset($websiteId)) ? $this->_getRequest()->getWebsiteId() : $websiteId;

        return Mage::getResourceModel('astrio_pickpoint/postamat')
            ->getPostamatCodeByCity($city, $websiteId);
    }

    /**
     * Returns PickPoint postamat code from the request
     *
     * @return string
     */
    protected function _getPostamatCodeFromRequest()
    {
        return (string) Mage::app()->getFrontController()->getRequest()->getPost(self::REQUEST_FIELD_NAME);
    }

    /**
     * Returns shipping method price by PickPoint zone code
     *
     * @param string|integer $zone PickPoint zone code
     *
     * @return float
     */
    protected function _getShippingMethodPriceByZone($zone)
    {
        return doubleval($this->getConfigData(self::CONFIG_SHIPPING_RATE_REGION_PREFIX . $zone));
    }

    /**
     * Returns request model
     *
     * @return Mage_Shipping_Model_Rate_Request|null
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns default city for PickPoint postamat selection
     *
     * @return string
     */
    protected function _getDefaultCityName()
    {
        return $this->_getApiInstance()->getApiOriginCity();
    }

    /**
     * Returns PickPoint API instance
     *
     * @return Astrio_Pickpoint_Model_Api|null
     */
    protected function _getApiInstance()
    {
        if (!isset($this->_apiInstance)) {
            $this->_apiInstance = Mage::helper('astrio_pickpoint')
                ->getApiInstance($this->_getRequest()->getStoreId());
        }

        return $this->_apiInstance;
    }

    /**
     * Returns PickPoint postamat collection model
     *
     * @return Astrio_Pickpoint_Model_Resource_Postamat_Collection
     */
    protected function _getPostamatCollection()
    {
        return Mage::getResourceModel('astrio_pickpoint/postamat_collection')
            //->addFieldToSelect(array('pt_number', 'city_name', 'address'))
            //->joinZones(Mage::app()->getStore()->getWebsiteId(), '')
            ->addOrder('main_table.region', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addOrder('main_table.city_name', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addOrder('main_table.address', Varien_Data_Collection::SORT_ORDER_ASC);
    }
}
