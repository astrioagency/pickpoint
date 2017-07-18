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
 * Event observer
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Observer
{
    /**
     * Updates order shipping description before save
     * Executes on global "sales_model_service_quote_submit_before" event
     *
     * @param Varien_Event_Observer $observer Event observer object
     *
     * @return void
     */
    public function updateShippingDescription(Varien_Event_Observer $observer)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getData('quote');

        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getData('order');

        $shippingMethod = $order->getShippingMethod(true);

        if (Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE == $shippingMethod->getCarrierCode()) {

            /* @var $carrier Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint */
            $carrier = $order->getShippingCarrier()->setStore($order->getStore());

            /* @var $postamat Astrio_Pickpoint_Model_Postamat */
            $postamat = Mage::getModel('astrio_pickpoint/postamat')
                ->loadByCode($shippingMethod->getMethod());

            $order->setShippingDescription($carrier->getCarrierTitle() . ' - ' . $postamat->getDescription());
        }
    }

    /**
     * Updates agent's shipping address (MoySklad orders export)
     * Executes on global "astrio_moysklad_order_export_get_agent" event
     *
     * @param Varien_Event_Observer $observer Event observer object
     *
     * @return void
     */
    public function updateMsOrderAgentShippingAddress(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getData('order');

        /* @var $agent Astrio_MoySklad_Model_Api_Entity_Counterparty */
        $agent = $observer->getEvent()->getData('agent');

        $shippingMethod = $order->getShippingMethod(true);

        if (Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE == $shippingMethod->getCarrierCode()) {

            /* @var $carrier Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint */
            $carrier = $order->getShippingCarrier()->setStore($order->getStore());

            /* @var $postamat Astrio_Pickpoint_Model_Postamat */
            $postamat = Mage::getModel('astrio_pickpoint/postamat')
                ->loadByCode($shippingMethod->getMethod());

            if ($postamat) {

                // Compose actual address
                $actualAddress = sprintf('%s (%s) - %s', $carrier->getCarrierTitle(), $postamat->getPtNumber(), $postamat->getFullAddress());

            } else {

                // If, by some reason, postamat model is not available use shipping description
                $actualAddress = str_replace(
                    $carrier->getCarrierTitle(),
                    $carrier->getCarrierTitle() . ' (' . $shippingMethod->getMethod() . ')',
                    $order->getShippingDescription()
                );
            }

            if ($actualAddress != $agent->getActualAddress()) {
                $agent->setActualAddress($actualAddress);
            }
        }
    }

    /**
     * Updates PickPoint postamats list
     * Handles "astrio_pickpoint_update_postamats_list" cron job
     *
     * @param Mage_Cron_Model_Schedule $schedule Crontab schedule model
     *
     * @return void
     * @throws Exception
     */
    public function cronUpdatePostamatsList(Mage_Cron_Model_Schedule $schedule)
    {
        /* @var $helper Astrio_Pickpoint_Helper_Data */
        $helper = Mage::helper('astrio_pickpoint');

        $api = $helper->getApiInstance();

        // Get postamat list from PickPoint service API
        $postamatList = $api->actionGetPostamatList();

        if (
            !empty($postamatList)
            && is_array($postamatList)
        ) {
            // Clear postamat list only if there is something to add
            Mage::getResourceModel('astrio_pickpoint/postamat')->truncate();

            // Update postamat list (save into DB)
            $helper->bulkArrayUpdate(
                $postamatList,
                $helper->getImportBulkPostamat(),
                array(Mage::getResourceModel('astrio_pickpoint/postamat'), 'mapAndInsertMultiple')
            );

            $schedule->setMessages(
                $helper->__('Postamat list has been successfully updated.')
            );

        } else {

            throw new Exception(
                $helper->__('Error: postamat list is empty.')
            );
        }
    }

    /**
     * Updates PickPoint cities list
     * Handles "astrio_pickpoint_update_cities_list" cron job
     *
     * @param Mage_Cron_Model_Schedule $schedule Crontab schedule model
     *
     * @return void
     * @throws Exception
     */
    public function cronUpdateCitiesList(Mage_Cron_Model_Schedule $schedule)
    {
        /* @var $helper Astrio_Pickpoint_Helper_Data */
        $helper = Mage::helper('astrio_pickpoint');

        $api = $helper->getApiInstance();

        // Get cities list from PickPoint service API
        $citiesList = $api->actionGetCityList();

        if (
            !empty($citiesList)
            && is_array($citiesList)
        ) {
            // Clear cities list only if there is something to add
            Mage::getResourceModel('astrio_pickpoint/city')->truncate();

            // Update cities list (save into DB)
            $helper->bulkArrayUpdate(
                $citiesList,
                $helper->getImportBulkCities(),
                array(Mage::getResourceModel('astrio_pickpoint/city'), 'mapAndInsertMultiple')
            );

            $schedule->setMessages(
                $helper->__('Cities list has been successfully updated.')
            );

        } else {

            throw new Exception(
                $helper->__('Error: cities list is empty.')
            );
        }
    }

    /**
     * Updates PickPoint zones list
     * Handles "astrio_pickpoint_update_zones_list" cron job
     *
     * @param Mage_Cron_Model_Schedule $schedule Crontab schedule model
     *
     * @return void
     * @throws Exception
     */
    public function cronUpdateZonesList(Mage_Cron_Model_Schedule $schedule)
    {
        /* @var $helper Astrio_Pickpoint_Helper_Data */
        $helper = Mage::helper('astrio_pickpoint');

        $api = $helper->getApiInstance();

        $_errors = $_messages = array();

        // Update zones list (by website)

        $websites = Mage::app()->getWebsites(false, true);

        if (!empty($websites)) {

            /* @var $zoneResource Astrio_Pickpoint_Model_Resource_Zone */
            $zoneResource = Mage::getResourceModel('astrio_pickpoint/zone');

            // Clear zones list
            $zoneResource->truncate();

            foreach ($websites as $code => $website) {
                /* @var $website Mage_Core_Model_Website */

                $api->setStore($website->getDefaultStore());

                $originCity = $api->getApiOriginCity();

                if (!empty($originCity)) {

                    if ($api->actionLogin()) {

                        // Get zone rates list from PickPoint service API
                        $zones = $api->actionGetZone($originCity);

                        $api->actionLogout();

                        if (!empty($zones) && is_array($zones)) {

                            if (!$helper->checkZoneRatesList($zones)) {

                                // Error: zone rates list does not have zone IDs or delivery time frames
                                $_errors[] = $helper->__(
                                    'Zone rates list for "%s" website and origin city (%s) is not valid.',
                                    $website->getName(),
                                    $originCity
                                );

                            } else {

                                // Add website ID for all zone rates
                                foreach ($zones as $k => $v) {
                                    if (!$v->ToPT) { // fix for empty terminal code
                                        unset($zones[$k]);
                                        continue;
                                    }
                                    $zones[$k] = $helper->mapArrayKeys($v, $zoneResource->getApiFieldsMapping());
                                    $zones[$k]['website_id'] = $website->getId();
                                }

                                // Update zone rates list (save into DB)
                                $helper->bulkArrayUpdate(
                                    $zones,
                                    $helper->getImportBulkZones(),
                                    array($zoneResource, 'insertOnDuplicate')
                                );

                                $_messages[] = $helper->__(
                                    'Zone rates list for "%s" website has been successfully updated.',
                                    $website->getName()
                                );
                            }

                        } else {

                            // Error: no zone rates
                            $_errors[] = $helper->__(
                                'Zone rates list for "%s" website and origin city (%s) is empty.',
                                $website->getName(),
                                $originCity
                            );
                        }

                    } elseif ($api->hasError()) {

                        // Error: PickPoint service API error
                        $_errors[] = $helper->__(
                            'Zone rates list update for "%s" website error: %s',
                            $website->getName(),
                            $api->getError()->getMessage()
                        );
                    }
                }
            }
        }

        if (!empty($_errors) && empty($_messages)) {

            // Report errors only
            throw new Exception(
                implode('; ' . PHP_EOL, $_errors)
            );

        } else {

            $schedule->setMessages(
                implode('; ' . PHP_EOL, $_errors)
                . PHP_EOL
                . implode('; ' . PHP_EOL, $_messages)
            );
        }
    }
}
