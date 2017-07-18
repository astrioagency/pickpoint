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
 * PickPoint shipping method info/additional data block 
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Block_Checkout_Shipping_Method_Pickpoint extends Mage_Core_Block_Template
{
    /**
     * Additional region sorting order
     *
     * @var array
     */
    protected static $_sortOrder = array(
        'default'              => 100,
        'Москва'              => 10,
        'Санкт-Петербург'    => 20,
        'Московская обл.'    => 30,
        'Ленинградская обл.' => 40,
    );

    /**
     * City to region translation
     *
     * @var array
     */
    protected static $_cityToRegion = array(
        'Московская обл.:::Москва'             => true,
        'Ленинградская обл.:::Санкт-Петербург' => true,
    );

    /**
     * Grouped shipping rates cache
     *
     * @var null|array
     */
    protected $_groupedAllShippingRates = null;

    /**
     * Prepared postamat list cache
     *
     * @var null|array
     */
    protected $_postamatList = null;

    /**
     * Quote model
     *
     * @var Mage_Sales_Model_Quote|null
     */
    protected $_quote = null;

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Returns all grouped shipping rates
     *
     * @return array|null
     */
    protected function getGroupedAllShippingRates()
    {
        if (
            isset($this->_quote)
            && !isset($this->_groupedAllShippingRates)
        ) {
            $this->_groupedAllShippingRates = $this->_quote->getShippingAddress()
                ->getGroupedAllShippingRates();
        }

        return $this->_groupedAllShippingRates;
    }

    /**
     * Returns selected PickPoint postamat number (code)
     *
     * @return null|string
     */
    public function getSelectedShippingRateCode()
    {
        $result = null;

        $shippingRates = $this->getGroupedAllShippingRates();

        $carrierCode = Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE;

        if (
            !empty($shippingRates)
            && isset($shippingRates[$carrierCode])
        ) {
            $result = $shippingRates[$carrierCode][0]->getMethod();
        }

        return $result;
    }

    /**
     * Returns postamat selector HTML
     *
     * @param string  $selectedPtCode Selected PickPoint postamat code
     * @param boolean $emptyOption    Flag: display empty option or not (OPTIONAL)
     *
     * @return string
     */
    public function getPostamatSelectorHtml($selectedPtCode = null, $emptyOption = false)
    {
        $html = '';

        if (!isset($selectedPtCode)) {
            $selectedPtCode = $this->getSelectedShippingRateCode();
        }

        $postamatList = $this->getPreparedPostamatList();

        if (!empty($postamatList)) {

            $html .= '<select id="' . $this->_getPickpointSelectorName() . '" name="' . $this->_getPickpointSelectorName() . '">' . PHP_EOL;

            if ($emptyOption) {
                // Display empty option
                $html .= '<option value="">' . $this->__('Please, select postamat') . '</option>' . PHP_EOL;
            }

            foreach ($postamatList as $region) {

                $html .= '<optgroup label="' . $this->escapeHtml($region['name']) . '">' . PHP_EOL;

                foreach ($region['postamats'] as $value => $label) {
                    $html .= '<option value="' . $value . '"'
                        . ((isset($selectedPtCode) && $selectedPtCode == $value) ? ' selected="selected"' : '')
                        . '>' . $this->escapeHtml($label) . '</option>';
                }

                $html .= '</optgroup>' . PHP_EOL;
            }

            $html .= '</select>' . PHP_EOL;
        }

        return $html;
    }

    /**
     * Returns "true" if postamat list is not empty, "false" otherwise
     *
     * @return boolean
     */
    public function hasPreparedPostamatList()
    {
        $list = $this->getPreparedPostamatList();

        return (0 < count($list));
    }

    /**
     * Prepares PickPoint postamat list
     *
     * @return array
     */
    public function getPreparedPostamatList()
    {
        if (!isset($this->_postamatList)) {

            $result = array();

            $collection = $this->_getPostamatCollection();

            if (0 < count($collection)) {

                foreach ($collection as $postamat) {

                    /* @var $postamat Astrio_Pickpoint_Model_Postamat */

                    $_cityToRegionKey = $postamat->getRegion() . ':::' . $postamat->getCityName();

                    $region = (isset(self::$_cityToRegion[$_cityToRegionKey]))
                        ? $postamat->getCityName()
                        : $postamat->getRegion();

                    if (!isset($result[$region])) {
                        $result[$region] = array(
                            'name' => $region,
                            'postamats' => array(),
                        );
                    }

                    $result[$region]['postamats'][$postamat->getPtNumber()] = $postamat->getCityAddress();
                }

                // Sort by regions' significance
                usort($result, array($this, '_compareRegions'));
            }

            $this->_postamatList = $result;
        }

        return $this->_postamatList;
    }

    /**
     * Returns PickPoint postamat collection
     *
     * @return Astrio_Pickpoint_Model_Resource_Postamat_Collection
     */
    protected function _getPostamatCollection()
    {
        /* @var $collection Astrio_Pickpoint_Model_Resource_Postamat_Collection */
        return Mage::getResourceModel('astrio_pickpoint/postamat_collection')
            ->addFieldToSelect(array('pt_number', 'region', 'city_name', 'address'))
            ->joinZones(Mage::app()->getStore()->getWebsiteId(), '')
            ->addOrder('main_table.region', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addOrder('main_table.city_name', Varien_Data_Collection::SORT_ORDER_ASC)
            ->addOrder('main_table.address', Varien_Data_Collection::SORT_ORDER_ASC);
    }

    /**
     * Returns PickPoint field name
     *
     * @return string
     */
    protected function _getPickpointSelectorName()
    {
        return Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::REQUEST_FIELD_NAME;
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
     * Returns "true" if block is visible
     *
     * @return boolean
     */
    protected function _isVisible()
    {
        return (
            $this->hasPreparedPostamatList()
            && $this->getSelectedShippingRateCode()
        );
    }

    /**
     * Renders template (block HTML)
     *
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->_isVisible()) ? parent::_toHtml() : '';
    }
}
