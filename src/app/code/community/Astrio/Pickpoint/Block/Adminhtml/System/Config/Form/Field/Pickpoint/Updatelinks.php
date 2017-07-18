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
 * PickPoint update links block (config field)
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Block_Adminhtml_System_Config_Form_Field_Pickpoint_Updatelinks
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('astrio/pickpoint/system/config/form/field/pickpoint/update_links.phtml');
        }
    }

    /**
     * Renders form element field
     *
     * @param Varien_Data_Form_Element_Abstract $element Element model
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->renderView();
    }

    /**
     * Returns update packstations list URL
     * (helper, used in template)
     *
     * @return string
     */
    public function getUpdatePackstationsListUrl()
    {
        return $this->getUrl('*/astrio_pickpoint/updatePackstations');
    }

    /**
     * Returns update cities list URL
     * (helper, used in template)
     *
     * @return string
     */
    public function getUpdateCitiesListActionUrl()
    {
        return $this->getUrl('*/astrio_pickpoint/updateCitiesList');
    }

    /**
     * Returns update zones rate list URL
     * (helper, used in template)
     *
     * @return string
     */
    public function getUpdateZonesRateListUrl()
    {
        return $this->getUrl('*/astrio_pickpoint/updateZonesRate');
    }
}
