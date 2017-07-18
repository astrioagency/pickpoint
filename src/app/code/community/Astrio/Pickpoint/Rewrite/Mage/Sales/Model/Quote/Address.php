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
 * Sales Quote address model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Rewrite_Mage_Sales_Model_Quote_Address
    extends Mage_Sales_Model_Quote_Address
{
    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item Quote item model
     *
     * @return boolean
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        $result = parent::requestShippingRates($item);

        if (
            !$result
            && Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE == $this->_getShippingCarrierCode()
        ) {
            foreach ($this->getAllShippingRates() as $rate) {
                /* @var $rate Mage_Sales_Model_Quote_Address_Rate */
                if (Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE == $rate->getCarrier()) {

                    $this->setShippingMethod($rate->getCode());

                    Mage::helper('astrio_pickpoint')->setSessionPostamatData(
                        array(
                            'destCity' => ($this->getCity()) ? $this->getCity() : $this->getRegion(),
                            'code' => $rate->getMethod(),
                        )
                    );

                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        $this->setShippingAmount($rate->getPrice());
                    }

                    $result = true;

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns code of the selected shipping carrier
     *
     * @return string
     */
    protected function _getShippingCarrierCode()
    {
        $code = '';

        if ($this->getShippingMethod()) {
            $method = explode('_', $this->getShippingMethod(), 2);
            $code = $method[0];
        }

        return $code;
    }
}
