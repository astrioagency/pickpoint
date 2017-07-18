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
 * PickPoint API model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Api_Pickpoint
{
    /**
     * API service URLs
     */
    const API_TEST_URL = 'e-solution.pickpoint.ru/apitest/';
    const API_LIVE_URL = 'e-solution.pickpoint.ru/api/';

    /**
     * Request timeout
     */
    const REQUEST_TIMEOUT = 60;

    /**
     * API actions
     */
    const API_ACTION_LOGIN         = 'login';
    const API_ACTION_LOGOUT        = 'logout';
    const API_ACTION_GET_ZONE      = 'getzone';
    const API_ACTION_POSTAMAT_LIST = 'postamatlist';
    const API_ACTION_CITY_LIST     = 'citylist';
    const API_ACTION_GET_STATES    = 'getstates';

    const API_ACTION_CALC_TARIFF   = 'calctariff';

    /**
     * API request methods
     */
    const API_REQUEST_METHOD_POST = 'POST';
    const API_REQUEST_METHOD_GET  = 'GET';

    /**
     * PickPoint API session ID
     *
     * @var null|string
     */
    protected $_sessionId = null;

    /**
     * Flag: is API in test mode or not
     *
     * @var boolean
     */
    protected $_isTestMode = false;

    /**
     * Flag: use secure connection or not
     */
    protected $_useSecure = false;

    /**
     * Constructor
     *
     * @param boolean $testMode Flag: use test model (OPTIONAL)
     * @param boolean $secure   Flag: use secure connection (OPTIONAL)
     *
     * @return self
     */
    public function __construct($testMode = false, $secure = false)
    {
        $this->setTestMode($testMode);
        $this->setUseSecureConnection($secure);
    }

    /**
     * Returns PickPoint API session ID
     *
     * @return null|string
     */
    protected function _getSessionId()
    {
        return $this->_sessionId;
    }

    /**
     * Sets PickPoint API session ID
     *
     * @param string $value Session ID (OPTIONAL)
     *
     * @return self
     */
    protected function _setSessionId($value = null)
    {
        $this->_sessionId = $value;

        return $this;
    }

    /**
     * Returns "true" if API is in test mode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->_isTestMode;
    }

    /**
     * Sets test mode flag
     *
     * @param boolean $value Value
     *
     * @return self
     */
    public function setTestMode($value)
    {
        $this->_isTestMode = (bool) $value;

        return $this;
    }

    /**
     * Returns "true" if API should use secure connection
     *
     * @return boolean
     */
    public function getUseSecureConnection()
    {
        return $this->_useSecure;
    }

    /**
     * Sets use secure connection flag
     *
     * @param boolean $value Value
     *
     * @return self
     */
    public function setUseSecureConnection($value)
    {
        $this->_useSecure = (bool) $value;

        return $this;
    }

    /**
     * Returns API server URL (test mode does not support secure connection)
     *
     * @param string $action Action name
     *
     * @return string
     */
    public function getServerUrl($action = '')
    {
        return (($this->getUseSecureConnection() && !$this->isTestMode()) ? 'https://' : 'http://')
        . (($this->isTestMode()) ? static::API_TEST_URL : static::API_LIVE_URL)
        . $action;
    }

    /**
     * API action: login
     * Creates PickPoint API session
     *
     * @param string $login    API login
     * @param string $password API password
     *
     * @return boolean
     */
    public function actionLogin($login, $password)
    {
        $data = array(
            'Login'    => $login,
            'Password' => $password,
        );

        $response = $this->_actionRequest(static::API_ACTION_LOGIN, $data);

        if (isset($response->SessionId)) {
            $this->_setSessionId($response->SessionId);
        }

        return (isset($response->SessionId));
    }

    /**
     * API action: logout
     * Closes PickPoint API session
     *
     * @return boolean
     */
    public function actionLogout()
    {
        $data = array(
            'SessionId' => $this->_getSessionId(),
        );

        $response = $this->_actionRequest(static::API_ACTION_LOGOUT, $data);

        if (
            isset($response->Success)
            && true == $response->Success
        ) {
            // Clear session var
            $this->_setSessionId(null);
        }

        return (isset($response->Success) && true == $response->Success);
    }

    /**
     * API action: get zone
     * Returns cities zones
     *
     * @param string      $originCity Origin city
     * @param null|string $dstPt      Destination postamat number
     *
     * @return boolean|array
     */
    public function actionGetZone($originCity, $dstPt = null)
    {
        $data = array(
            'SessionId' => $this->_getSessionId(),
            'FromCity'  => $originCity,
        );

        if (isset($dstPt) && !empty($dstPt)) {
            $data['ToPT'] = $dstPt;
        }

        $response = $this->_actionRequest(static::API_ACTION_GET_ZONE, $data);

        return (isset($response->Zones) && is_array($response->Zones))
            ? $response->Zones
            : false;
    }

    /**
     * API action: postamatlist (works without session)
     * Returns postamat list
     *
     * @return array|boolean
     */
    public function actionGetPostamatList()
    {
        $response = $this->_actionRequest(static::API_ACTION_POSTAMAT_LIST, array(), static::API_REQUEST_METHOD_GET);

        return (!empty($response) && is_array($response)) ? $response : false;
    }

    /**
     * API action: citylist (works without session)
     * Returns cities list
     *
     * @return array|boolean
     */
    public function actionGetCityList()
    {
        $response = $this->_actionRequest(static::API_ACTION_CITY_LIST, array(), static::API_REQUEST_METHOD_GET);

        return (!empty($response) && is_array($response)) ? $response : false;
    }

    /**
     * API action: getstates (works without session)
     * Returns shipping statuses list
     *
     * @return boolean|array
     */
    public function actionGetStates()
    {
        $response = $this->_actionRequest(static::API_ACTION_GET_STATES, array(), static::API_REQUEST_METHOD_GET);

        return (!empty($response) && is_array($response)) ? $response : false;
    }

    /**
     * API action: calctariff
     * Calculates shipping rate for a package
     *
     * @param array $params Parameters list
     *
     * @return boolean|array
     */
    public function actionCalcTariff($params = array())
    {
        $data = array(
            'SessionId'    => $this->_getSessionId(),
            'IKN'          => '',
            'FromCity'     => '',
            'FromRegion'   => '',
            'PTNumber'     => '',
            'Length'       => 0,
            'Depth'        => 0,
            'Width'        => 0,
            'Weight'       => 0,
        );

        $optionalParams = array(
            'InvoiceNumber',
            'EncloseCount',
        );

        if (is_array($params) && !empty($params)) {
            // Replace default params
            foreach ($params as $k => $v) {
                if (isset($data[$k]) || isset($optionalParams[$k])) {
                    $data[$k] = $v;
                }
            }
        }

        $response = $this->_actionRequest(static::API_ACTION_CALC_TARIFF, $data);

        return (isset($response->Services)) ? $response->Services : false;
    }

    /**
     * Performs HTTP request
     *
     * @param string $action Action name
     * @param array  $data   Request data
     * @param string $method Method name
     *
     * @return boolean|array
     *
     * @throws Exception
     */
    protected function _actionRequest($action, $data, $method = self::API_REQUEST_METHOD_POST)
    {
        $serverUrl = $this->getServerUrl($action);

        // Init cURL session
        $ch = curl_init();

        if (false === $ch) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        curl_setopt($ch, CURLOPT_URL, $serverUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, static::REQUEST_TIMEOUT);

        if (static::API_REQUEST_METHOD_POST == $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }

        $response = curl_exec($ch);

        if (false === $response) {

            $exception = new Exception(curl_error($ch), curl_errno($ch));

            // Close cURL session
            curl_close($ch);

            throw $exception;
        }

        // Close cURL session
        curl_close($ch);

        if (empty($response)) {

            $result = new stdClass();
            $result->ErrorMessage = 'No response';

        } else {

            $result = json_decode($response);
        }

        return $result;
    }
}
