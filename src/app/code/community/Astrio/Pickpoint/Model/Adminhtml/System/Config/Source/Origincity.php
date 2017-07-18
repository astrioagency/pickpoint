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
 * Adminhtml system config model: origin city selector
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Adminhtml_System_Config_Source_Origincity
{
    /**
     * Returns options as array
     *
     * @param boolean $isMultiselect Flag: is multi-select enabled or not (OPTIONAL)
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        return array(
            array(
                'value' => Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::ORIGIN_CITY_USE_SHIPPING,
                'label' => Mage::helper('astrio_pickpoint')->__('Use origin city from shipping settings')
            ),
            array(
                'value' => Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::ORIGIN_CITY_USE_SELECTED,
                'label' => Mage::helper('astrio_pickpoint')->__('Select city')
            ),
        );
    }
}
