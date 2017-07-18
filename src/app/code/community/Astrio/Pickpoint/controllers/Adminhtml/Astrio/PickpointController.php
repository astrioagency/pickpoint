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
 * PickPoint update entities controller
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Adminhtml_Astrio_PickpointController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Action: updates packstations list
     *
     * @return void
     */
    public function updatePackstationsAction()
    {
        // NOTE: packstations (postamat) list is global and does not require PickPoint API session

        try {

            $api = $this->_getHelper()->getApiInstance();

            $postamatList = $api->actionGetPostamatList();

            if (
                !empty($postamatList)
                && is_array($postamatList)
            ) {
                // Clear postamat list only if there is something to add
                Mage::getResourceModel('astrio_pickpoint/postamat')->truncate();

                // Update postamat list (save into DB)
                $this->_getHelper()->bulkArrayUpdate(
                    $postamatList,
                    $this->_getHelper()->getImportBulkPostamat(),
                    array(Mage::getResourceModel('astrio_pickpoint/postamat'), 'mapAndInsertMultiple')
                );

                $this->_getSession()->addSuccess(
                    $this->__('Postamat list has been successfully updated.')
                );

            } elseif ($api->hasError()) {

                // Display PickPoint API error
                $this->_getSession()->addError($this->__(
                    'Postamat list update error: %s', $api->getError()->getMessage()
                ));

            } else {

                $this->_getSession()->addError($this->__(
                    'Postamat list update error: %s', $this->__('Postamat list is empty')
                ));
            }

        } catch (Mage_Core_Exception $e) {

            $this->_getSession()->addError($e->getMessage());

        } catch (Exception $e) {

            $this->_getSession()->addException($e, $e->getMessage());
        }

        $this->_completeAction();
    }

    /**
     * Action: update zones rate list
     *
     * @return void
     */
    public function updateZonesRateAction()
    {
        // Zones list depends on origin city which in turn depends on a website

        $websites = Mage::app()->getWebsites(false, true);

        if (!empty($websites)) {

            // Clear zones list
            Mage::getResourceModel('astrio_pickpoint/zone')->truncate();

            try {

                foreach ($websites as $code => $website) {
                    /* @var $website Mage_Core_Model_Website */
                    $this->_updateZonesRateByStore($website->getDefaultStore());
                }

            } catch (Mage_Core_Exception $e) {

                $this->_getSession()->addError($e->getMessage());

            } catch (Exception $e) {

                $this->_getSession()->addException($e, $e->getMessage());
            }
        }

        $this->_completeAction();
    }

    /**
     * Update zones rate by store
     *
     * @param Mage_Core_Model_Store $store Store model
     *
     * @return boolean
     */
    protected function _updateZonesRateByStore(Mage_Core_Model_Store $store)
    {
        $api = $this->_getHelper()->getApiInstance($store);

        $originCity = $api->getApiOriginCity();

        $result = false;

        if (!empty($originCity)) {

            if ($api->actionLogin()) {

                // Get zone rates list from PickPoint API
                $zones = $api->actionGetZone($originCity);

                $api->actionLogout();

                if (!empty($zones) && is_array($zones)) {

                    if (!$this->_getHelper()->checkZoneRatesList($zones)) {

                        // Error: zone rates list does not have zone IDs or delivery time frames
                        $this->_getSession()->addError(
                            $this->__(
                                'Zone rates list for "%s" website and origin city (%s) is not valid.',
                                $store->getWebsite()->getName(),
                                $originCity
                            )
                        );

                    } else {

                        /* @var $zoneResource Astrio_Pickpoint_Model_Resource_Zone */
                        $zoneResource = Mage::getResourceModel('astrio_pickpoint/zone');

                        // Add website ID for all zone rates
                        foreach ($zones as $k => $v) {
                            if (!$v->ToPT) { // fix for epmty terminal code
                                unset($zones[$k]);
                                continue;
                            }

                            $zones[$k] = $this->_getHelper()->mapArrayKeys($v, $zoneResource->getApiFieldsMapping());
                            $zones[$k]['website_id'] = $store->getWebsiteId();
                        }

                        // Update zone rates list (save into DB)
                        $this->_getHelper()->bulkArrayUpdate(
                            $zones,
                            $this->_getHelper()->getImportBulkZones(),
                            array($zoneResource, 'insertOnDuplicate')
                        );

                        $this->_getSession()->addSuccess(
                            $this->__(
                                'Zone rates list for "%s" website has been successfully updated.',
                                $store->getWebsite()->getName()
                            )
                        );
                    }

                } else {

                    // Error: no zone rates
                    $this->_getSession()->addError(
                        $this->__(
                            'Zone rates list for "%s" website and origin city (%s) is empty.',
                            $store->getWebsite()->getName(),
                            $originCity
                        )
                    );
                }

            } elseif ($api->hasError()) {

                // Display PickPoint API error
                $this->_getSession()->addError(
                    $this->__(
                        'Zone rates list update for "%s" website error: %s',
                        $store->getWebsite()->getName(),
                        $api->getError()->getMessage()
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Action: update cities list
     *
     * @return void
     */
    public function updateCitiesListAction()
    {
        // NOTE: cities list is global and does not require PickPoint API session

        try {

            $api = $this->_getHelper()->getApiInstance();

            $citiesList = $api->actionGetCityList();

            if (
                !empty($citiesList)
                && is_array($citiesList)
            ) {
                // Clear cities list only if there is something to add
                Mage::getResourceModel('astrio_pickpoint/city')->truncate();

                // Update cities list (save into DB)
                $this->_getHelper()->bulkArrayUpdate(
                    $citiesList,
                    $this->_getHelper()->getImportBulkCities(),
                    array(Mage::getResourceModel('astrio_pickpoint/city'), 'mapAndInsertMultiple')
                );

                $this->_getSession()->addSuccess(
                    $this->__('Cities list has been successfully updated.')
                );

            } elseif ($api->hasError()) {

                // Display PickPoint API error
                $this->_getSession()->addError($this->__(
                    'Cities list update error: %s', $api->getError()->getMessage()
                ));

            } else {

                $this->_getSession()->addError($this->__(
                    'Cities list update error: %s', $this->__('Cities list is empty')
                ));
            }

        } catch (Mage_Core_Exception $e) {

            $this->_getSession()->addError($e->getMessage());

        } catch (Exception $e) {

            $this->_getSession()->addException($e, $e->getMessage());
        }

        $this->_completeAction();
    }

    /**
     * Completes an action
     *
     * @return void
     */
    protected function _completeAction()
    {
        if ($this->getRequest()->isAjax()) {

            // Request was made via Ajax
            $eventResponse = $this->_initAjaxResponse();

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($eventResponse->getData())
            );

        } else {

            $this->_redirect('*/system_config/edit', array('section' => 'carriers'));
        }
    }

    /**
     * Initializes and returns AJAX response
     *
     * @return Varien_Object
     */
    protected function _initAjaxResponse()
    {
        $this->_initLayoutMessages('adminhtml/session');

        return new Varien_Object(array(
            'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
        ));
    }

    /**
     * Returns PickPoint helper
     *
     * @return Astrio_Pickpoint_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('astrio_pickpoint');
    }

    /**
     * Checks ACL permissions
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return (
            parent::_isAllowed()
            && Mage::getSingleton('admin/session')->isAllowed('system/config/carriers')
        );
    }
}
