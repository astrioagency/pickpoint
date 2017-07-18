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
 * One page checkout processing model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Rewrite_Mage_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Saves quote shipping method
     *
     * @param string $shippingMethod Shipping method code
     *
     * @return array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (!empty($shippingMethod)) {

            list($carrierCode, $shippingMethodCode) = explode('_', $shippingMethod, 2);

            if (
                Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE == $carrierCode
                && !empty($shippingMethodCode)
                && !$this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod)
            ) {
                $savedData = Mage::helper('astrio_pickpoint')->getSessionPostamatData();

                /* @var $postamat Astrio_Pickpoint_Model_Postamat */
                $postamat = Mage::getModel('astrio_pickpoint/postamat')
                    ->setWebsiteId($this->getQuote()->getStore()->getWebsiteId())
                    ->load($shippingMethodCode, 'pt_number');

                if ($postamat && $postamat->getPtNumber()) {

                    // Force set shipping method, pickPoint postamat has been changed, rates must be recalculated

                    $savedData['code'] = $postamat->getPtNumber();

                    Mage::helper('astrio_pickpoint')->setSessionPostamatData($savedData);

                    $this->getQuote()
                        ->getShippingAddress()
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);

                    $this->getCheckout()
                        ->setStepData('shipping_method', 'allow', true);

                    $this->getQuote()->collectTotals();

                    return array(
                        'goto_section' => 'shipping_method',
                        'update_section' => array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml(),
                        )
                    );
                }
            }
        }

        return parent::saveShippingMethod($shippingMethod);
    }

    /**
     * Returns shipping method step HTML
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = Mage::getSingleton('core/layout');
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }
}
