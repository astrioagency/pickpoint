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
 * PickPoint city resource model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Resource_City extends Astrio_Pickpoint_Model_Resource_AbstractMapping
{
    /**
     * PickPoint API to DB fields mapping
     *
     * @var array
     */
    protected static $_fieldsMapping = array(
        'Id'         => 'city_id',     // varchar:20
        'Owner_Id'   => 'owner_id',    // varchar:20
        'Name'       => 'name',        // varchar:50
        'NameEng'    => 'name_eng',    // varchar:50
        'RegionName' => 'region_name', // varchar:50
    );

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('astrio_pickpoint/city', 'id');
    }
}
