<?php
/**
 * This file is part of the SevenShores/NetSuite library.
 *
 * @package    ryanwinchester/netsuite-php
 * @author     Ryan Winchester <fungku@gmail.com>
 * @copyright  Copyright (c) Ryan Winchester
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @link       https://github.com/ryanwinchester/netsuite-php
 * created:    2015-01-22  1:04 PM
 */

namespace NetSuite;

use NetSuite\Classes\ApplicationInfo;
use NetSuite\Classes\Passport;
use NetSuite\Classes\Preferences;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\SearchPreferences;
use SoapClient;
use SoapHeader;

class NetSuiteClient
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var SoapClient
     */
    private $client;
    /**
     * @var array
     */
    private $soapHeaders = array();

    /**
     * @param array $config
     * @param array $options
     * @param SoapClient $client
     */
    public function __construct($config, $options = array(), $client = null)
    {
        $this->config = $config;
        $options = $this->createOptions($this->config, $options);
        $wsdl = $this->createWsdl($this->config);
        $this->client = $client ?: new SoapClient($wsdl, $options);
    }

    public static function createFromEnv($options = array(), $client = null)
    {
        $config = array(
            'endpoint' => getenv('NETSUITE_ENDPOINT') ?: '2016_1',
            'host' => getenv('NETSUITE_HOST') ?: 'https://webservices.sandbox.netsuite.com',
            'email' => getenv('NETSUITE_EMAIL'),
            'password' => getenv('NETSUITE_PASSWORD'),
            'role' => getenv('NETSUITE_ROLE') ?: '3',
            'account' => getenv('NETSUITE_ACCOUNT'),
            'app_id' => getenv('NETSUITE_APP_ID') ?: '4AD027CA-88B3-46EC-9D3E-41C6E6A325E2',
            'logging' => getenv('NETSUITE_LOGGING'),
            'log_path' => getenv('NETSUITE_LOG_PATH') ?: '',
        );

        return new static($config, $options, $client);
    }

    /**
     * Make the SOAP call!
     *
     * @param string $operation
     * @param mixed $parameter
     * @return mixed
     */
    protected function makeSoapCall($operation, $parameter)
    {
        $this->fixWtfCookieBug();
        $this->setApplicationInfo($this->config['app_id']);
        $this->addHeader("passport", $this->createPassportFromConfig($this->config));

        try {
            $response = $this->client->__soapCall($operation, array($parameter), null, $this->soapHeaders);
            $this->logSoapCall($operation);
            return $response;
        } catch (\Exception $e) {
            $this->logSoapCall($operation);
            throw $e;
        }
    }

    /**
     * Create the options array.
     *
     * @param array $config
     * @param array $overrides
     * @return array
     */
    private function createOptions($config, $overrides = array())
    {
        return array_merge(array(
            'classmap' => require __DIR__."/includes/classmap.php",
            'trace' => 1,
            'connection_timeout' => 5,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'location' => $config['host']."/services/NetSuitePort_".$config['endpoint'],
            'keep_alive' => false,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'user_agent' => "PHP-SOAP/".phpversion()." + ryanwinchester/netsuite-php",
        ), $overrides);
    }

    /**
     * Build the WSDL address from the config.
     *
     * @param array $config
     * @return string
     */
    private function createWsdl($config)
    {
        return $config['host'].'/wsdl/v'.$config['endpoint'].'_0/netsuite.wsdl';
    }

    /**
     * Create the Passport.
     *
     * @param array $config
     * @return Passport
     */
    private function createPassportFromConfig($config)
    {
        $passport = new Passport();
        $passport->account = $config['account'];
        $passport->email = $config['email'];
        $passport->password = $config['password'];
        $passport->role = new RecordRef();
        $passport->role->internalId = $config['role'];

        return $passport;
    }

    /**
     * Add a header by name.
     *
     * @param string $header
     * @param mixed $value
     */
    public function addHeader($header, $value)
    {
        $this->soapHeaders[$header] = new SoapHeader("ns", $header, $value);
    }

    /**
     * Remove a header by name.
     *
     * @param string $header
     */
    public function clearHeader($header)
    {
        unset($this->soapHeaders[$header]);
    }

    /**
     * Set the application id.
     *
     * @param string $appId
     */
    public function setApplicationInfo($appId = null)
    {
        $applicationInfo = new ApplicationInfo();
        $applicationInfo->applicationId = $appId;
        $this->addHeader("applicationInfo", $applicationInfo);
    }

    /**
     * Set preferences header.
     *
     * @param bool $warningAsError
     * @param bool $disableMandatoryCustomFieldValidation
     * @param bool $disableSystemNotesForCustomFields
     * @param bool $ignoreReadOnlyFields
     */
    public function setPreferences(
        $warningAsError = false,
        $disableMandatoryCustomFieldValidation = false,
        $disableSystemNotesForCustomFields = false,
        $ignoreReadOnlyFields = false
    ) {
        $preferences = new Preferences();
        $preferences->warningAsError = $warningAsError;
        $preferences->disableMandatoryCustomFieldValidation = $disableMandatoryCustomFieldValidation;
        $preferences->disableSystemNotesForCustomFields = $disableSystemNotesForCustomFields;
        $preferences->ignoreReadOnlyFields = $ignoreReadOnlyFields;
        $this->addHeader("preferences", $preferences);
    }

    /**
     * Clear preferences header.
     */
    public function clearPreferences()
    {
        $this->clearHeader("preferences");
    }

    /**
     * Set the search preferences header.
     *
     * @param bool $bodyFieldsOnly
     * @param int $pageSize
     * @param bool $returnSearchColumns
     */
    public function setSearchPreferences($bodyFieldsOnly = true, $pageSize = 50, $returnSearchColumns = true)
    {
        $preferences = new SearchPreferences();
        $preferences->bodyFieldsOnly = $bodyFieldsOnly;
        $preferences->pageSize = $pageSize;
        $preferences->returnSearchColumns = $returnSearchColumns;

        $this->addHeader("searchPreferences", $preferences);
    }

    /**
     * Clear the search preferences.
     */
    public function clearSearchPreferences()
    {
        $this->clearHeader("searchPreferences");
    }

    /**
     * SoapClient apparently always sends the JSESSIONID cookie.
     * So we'll just un-set it to prevent this.
     */
    private function fixWtfCookieBug()
    {
        $this->client->__setCookie("JSESSIONID");
    }

    /**
     * Turn request logging on or off.
     *
     * @param bool $on
     */
    public function logRequests($on = true)
    {
        $this->config['logging'] = $on;
    }

    /**
     * Set the logging path.
     *
     * @param string $logPath
     */
    public function setLogPath($logPath)
    {
        $this->config['log_path'] = $logPath;
    }

    /**
     * Log the last SOAP call.
     *
     * @param string $operation
     */
    private function logSoapCall($operation)
    {
        if ($this->config['logging'] && file_exists($this->config['log_path'])) {
            $logger = new Logger($this->config['log_path']);
            $logger->logSoapCall($this->client, $operation);
        }
    }
}
