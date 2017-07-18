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
 * PickPoint API wrapper model
 *
 * @category Astrio
 * @package  Astrio_Pickpoint
 * @author   Vladimir Khalzov <v.khalzov@astrio.net>
 */
class Astrio_Pickpoint_Model_Api extends Astrio_Pickpoint_Model_Api_Pickpoint
{
    /**
     * Log file name
     */
    const API_LOG_FILENAME = 'shipping_astrio_pickpoint.log';

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Store code
     *
     * @var string|null
     */
    protected $_store = null;

    /**
     * Error
     *
     * @var null|Exception
     */
    protected $_error = null;

    /**
     * Constructor
     *
     * @param array $params Parameters list (OPTIONAL)
     *
     * @return self
     */
    public function __construct($params = array())
    {
        if (isset($params['store']) && !empty($params['store'])) {
            $this->setStore($params['store']);
        }

        parent::__construct($this->getApiTestMode(), $this->getApiSecure());
    }

    /**
     * Returns current store code
     *
     * @return null|string
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Sets current code
     *
     * @param string $value Value (OPTIONAL)
     *
     * @return self
     */
    public function setStore($value = null)
    {
        $this->_store = $value;

        return $this;
    }

    /**
     * Returns error
     *
     * @return Exception|null
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Returns "true" if there is an error
     *
     * @return boolean
     */
    public function hasError()
    {
        return isset($this->_error);
    }

    /**
     * Returns carrier config field path
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function getCarrierConfigFieldPath($field)
    {
        return 'carriers/' . Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::CODE . '/' . $field;
    }

    /**
     * Returns carrier config
     *
     * @param string $field Field name
     *
     * @return mixed
     */
    public function getCarrierConfig($field)
    {
        return Mage::getStoreConfig($this->getCarrierConfigFieldPath($field), $this->getStore());
    }

    /**
     * Returns carrier config flag
     *
     * @param string $field Field name
     *
     * @return boolean
     */
    public function getCarrierConfigFlag($field)
    {
        return Mage::getStoreConfigFlag($this->getCarrierConfigFieldPath($field), $this->getStore());
    }

    /**
     * Returns PickPoint API login
     *
     * @return string
     */
    public function getApiLogin()
    {
        return (string) $this->getCarrierConfig('login');
    }

    /**
     * Returns PickPoint API password
     *
     * @return string
     */
    public function getApiPassword()
    {
        return (string) $this->getCarrierConfig('password');
    }

    /**
     * Returns PickPoint API client number
     *
     * @return string
     */
    public function getApiClientNumber()
    {
        return (string) $this->getCarrierConfig('client_number');
    }

    /**
     * Returns "true" if test mode flag is enabled
     *
     * @return boolean
     */
    public function getApiTestMode()
    {
        return $this->getCarrierConfigFlag('test_mode');
    }

    /**
     * Returns "true" if secure connection flag is enabled
     *
     * @return boolean
     */
    public function getApiSecure()
    {
        return $this->getCarrierConfigFlag('secure');
    }

    /**
     * Returns "true" if debug mode is enabled
     *
     * @return boolean
     */
    public function getApiDebug()
    {
        return $this->getCarrierConfigFlag('debug');
    }

    /**
     * Returns origin city for PickPoint API
     *
     * @return string
     */
    public function getApiOriginCity()
    {
        return ($this->useOriginCity())
            ? Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $this->getStore())
            : $this->getCarrierConfig('specific_origin_city');
    }

    /**
     * Returns "true" if origin city should be taken from shipping settings
     *
     * @return boolean
     */
    public function useOriginCity()
    {
        return (Astrio_Pickpoint_Model_Shipping_Carrier_Pickpoint::ORIGIN_CITY_USE_SHIPPING == $this->getCarrierConfig('origin_city'));
    }

    /**
     * API action: login (rewrite)
     * Creates PickPoint API session
     *
     * @param string $login    API login
     * @param string $password API password
     *
     * @return boolean
     */
    public function actionLogin($login = '', $password = '')
    {
        $login = (!empty($login)) ? $login : $this->getApiLogin();
        $password = (!empty($password)) ? $password : $this->getApiPassword();

        return parent::actionLogin($login, $password);
    }

    /**
     * Performs HTTP request (rewrite)
     *
     * @param string $action Action name
     * @param array  $data   Request data
     * @param string $method Method name
     *
     * @return boolean|array
     */
    protected function _actionRequest($action, $data, $method = self::API_REQUEST_METHOD_POST)
    {
        $serverUrl = $this->getServerUrl($action);

        // Save debug data
        $debugData = array(
            'request' => array(
                'url'    => $serverUrl,
                'method' => $method,
                'data'   => $data,
            ),
            'result' => null,
        );

        try {

            $httpClient = new Varien_Http_Client(
                $serverUrl,
                array(
                    'timeout' => static::REQUEST_TIMEOUT,
                )
            );

            if (static::API_REQUEST_METHOD_POST == $method) {
                $httpClient->setRawData(json_encode($data), 'application/json');
            }

            $response = $httpClient->request($method);

            $result = null;

            if ($response->isSuccessful()) {

                $result = json_decode($response->getBody());

                if (empty($result)) {
                    // Error: response body is empty
                    Mage::throwException(
                        Mage::helper('astrio_pickpoint')->__(
                            'Response error: body of the response for "%s" action is empty.', $action
                        )
                    );
                }

            } else {

                // Error: response error
                Mage::throwException(
                    Mage::helper('astrio_pickpoint')->__(
                        'Response error (%s): %s', $response->getStatus(), $response->getMessage()
                    )
                );
            }

            if (
                (isset($result->ErrorMessage) && !empty($result->ErrorMessage))
                || (isset($result->Error) && !empty($result->Error))
            ) {
                // Error: PickPoint API error occurred
                $errorMessage = (isset($result->ErrorMessage) && !empty($result->ErrorMessage))
                    ? $result->ErrorMessage
                    : $result->Error;

                Mage::throwException(
                    Mage::helper('astrio_pickpoint')->__('PickPoint API error: %s', $errorMessage)
                );
            }

            // Save debug data
            $debugData['result'] = $result;
        }
        catch (Exception $e) {

            $result = false;

            $this->_error = $e;

            // Save debug data
            $debugData['result'] = array(
                'error'    => $e->getMessage(),
            );
        }

        $this->_debug($debugData);

        return $result;
    }

    /**
     * Writes debug data to file
     *
     * @param array $debugData Debug data
     *
     * @return void
     */
    protected function _debug($debugData)
    {
        if ($this->getApiDebug()) {
            /* @var $logAdapter Mage_Core_Model_Log_Adapter */
            $logAdapter = Mage::getModel('core/log_adapter', static::API_LOG_FILENAME);
            $logAdapter->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
                ->log($debugData);
        }
    }
}
