<?php
class Unleaded_Integratee_Helper_Curl extends Mage_Core_Helper_Abstract
{
	protected $_attachments = array();

	protected $_response;

	protected $_creds;

	protected $_options = array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_HEADER => 0,);

	public function call($url, $method = Zend_Http_Client::POST, $params = array())
	{
		$adapter = new Zend_Http_Client_Adapter_Curl();
		$adapter->setConfig(array('curloptions' => $this->_options));

		$client = new Zend_Http_Client($url, array('adapter' => $adapter));

		if($method == Zend_Http_Client::POST){
			if($params) $client->setParameterPost($params);
		}elseif($method == Zend_Http_Client::GET){
			if($params) $client->setParameterGet($params);
		}

		if($this->_creds){
			$client->setAuth($this->_creds['user'], $this->_creds['pass']);
		}

		foreach($this->_attachments as $name => $file){
			$client->setFileUpload($file, $name);
		}

		try
		{
			$response = $client->request($method);
			if($response->getStatus() == 200){
				$this->_response = $response;
				return $this->_response->getBody();
			}
		}catch(Zend_Http_Client_Exception $ce){
			Mage::logException($ce);
		}catch(Exception $e){
			Mage::logException($e);
		}
		return false;
	}

	public function setOption($name, $val = null)
	{
		if(is_array($name)){
			foreach($name as $key => $value){
				$this->_options[$key] = $value;
			}
		}else{
			$this->_options[$name] = $val;
		}
		return $this;
	}

	public function addAttachment($name, $file)
	{
		$this->_attachments[$name] = $file;
		return $this;
	}

	public function setCredentials($user, $pass)
	{
		$this->_creds = array('user' => $user, 'pass' => $pass);
		return $this;
	}
}
