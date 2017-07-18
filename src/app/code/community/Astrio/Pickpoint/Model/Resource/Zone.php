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
 * PickPoint zone resource model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Resource_Zone extends Astrio_Pickpoint_Model_Resource_AbstractMapping
{
    /**
     * PickPoint API to DB fields mapping
     *
     * @var array
     */
    protected static $_fieldsMapping = array(
        'DeliveryMax' => 'delivery_max',
        'DeliveryMin' => 'delivery_min',
        'FromCity'    => 'origin_city',
        'Koeff'       => 'shipping_ratio',
        'ToCity'      => 'dst_city',
        'ToPT'        => 'pt_number',
        'Zone'        => 'zone',
    );

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('astrio_pickpoint/zone', 'id');
    }
}
