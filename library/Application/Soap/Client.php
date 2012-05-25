<?php
require_once 'Zend/Soap/Client.php';

class Application_Soap_Client extends Zend_Soap_Client {
	
	/**
	 * Set Options
	 *
	 * Allows setting options as an associative array of option => value pairs.
	 *
	 * @param  array|Zend_Config $options
	 * @return Zend_Soap_Client
	 * @throws Zend_SoapClient_Exception
	 */
	public function setOptions($options)
	{
		if($options instanceof Zend_Config) {
			$options = $options->toArray();
		}
	
		foreach ($options as $key => $value) {
			switch ($key) {
				case 'classmap':
				case 'classMap':
					$this->setClassmap($value);
					break;
				case 'encoding':
					$this->setEncoding($value);
					break;
				case 'soapVersion':
				case 'soap_version':
					$this->setSoapVersion($value);
					break;
				case 'wsdl':
					$this->setWsdl($value);
					break;
				case 'uri':
					$this->setUri($value);
					break;
				case 'location':
					$this->setLocation($value);
					break;
				case 'style':
					$this->setStyle($value);
					break;
				case 'use':
					$this->setEncodingMethod($value);
					break;
				case 'login':
					$this->setHttpLogin($value);
					break;
				case 'password':
					$this->setHttpPassword($value);
					break;
				case 'proxy_host':
					$this->setProxyHost($value);
					break;
				case 'proxy_port':
					$this->setProxyPort($value);
					break;
				case 'proxy_login':
					$this->setProxyLogin($value);
					break;
				case 'proxy_password':
					$this->setProxyPassword($value);
					break;
				case 'local_cert':
					$this->setHttpsCertificate($value);
					break;
				case 'passphrase':
					$this->setHttpsCertPassphrase($value);
					break;
				case 'compression':
					$this->setCompressionOptions($value);
					break;
				case 'stream_context':
					$this->setStreamContext($value);
					break;
				case 'features':
					$this->setSoapFeatures($value);
					break;
				case 'cache_wsdl':
					$this->setWsdlCache($value);
					break;
				case 'useragent':
				case 'userAgent':
				case 'user_agent':
					$this->setUserAgent($value);
					break;
	
				case 'connection_timeout':
					$this->_connection_timeout = $value;
				break;
	
					default:
						require_once 'Zend/Soap/Client/Exception.php';
						throw new Zend_Soap_Client_Exception('Unknown SOAP client option');
						break;
			}
		}
	
		return $this;
	}
	
	
	/**
	 * Return array of options suitable for using with SoapClient constructor
	 *
	 * @return array
	 */
	function getOptions()
	{
		$options = parent::getOptions();
		if (is_integer($this->_connection_timeout)) {
			$options["connection_timeout"] = $this->_connection_timeout;
		}
		return $options;
	}
	
	
	/**
	 * Initialize SOAP Client object
	 *
	 * @throws Zend_Soap_Client_Exception
	 */
	protected function _initSoapClientObject()
	{
		$wsdl = $this->getWsdl();
		$options = array_merge($this->getOptions(), array('trace' => false, 'exceptions' => 1));
		
		if ($wsdl == null) {
			if (!isset($options['location'])) {
				require_once 'Zend/Soap/Client/Exception.php';
				throw new Zend_Soap_Client_Exception('\'location\' parameter is required in non-WSDL mode.');
			}
			if (!isset($options['uri'])) {
				require_once 'Zend/Soap/Client/Exception.php';
				throw new Zend_Soap_Client_Exception('\'uri\' parameter is required in non-WSDL mode.');
			}
		} else {
			if (isset($options['use'])) {
				require_once 'Zend/Soap/Client/Exception.php';
				throw new Zend_Soap_Client_Exception('\'use\' parameter only works in non-WSDL mode.');
			}
			if (isset($options['style'])) {
				require_once 'Zend/Soap/Client/Exception.php';
				throw new Zend_Soap_Client_Exception('\'style\' parameter only works in non-WSDL mode.');
			}
		}
		unset($options['wsdl']);
			
		try {
			// we'll wait for the external vendor for 5 seconds.
			$default_socket_timeout = ini_get('default_socket_timeout');
			ini_set('default_socket_timeout', $options['connection_timeout']);
				
			$this->_soapClient = new Zend_Soap_Client_Common(array($this, '_doRequest'), $wsdl, $options);

			// reset the default_socket_timeout back
			ini_set('default_socket_timeout', $default_socket_timeout);
		} catch (SoapFault $e) {  
  	  		throw new Zend_Exception($e->getMessage());
		}
	}
}