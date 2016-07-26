<?php
class Unleaded_Integratee_Helper_Ee extends Mage_Core_Helper_Abstract
{
	public function inc($template,$ee_path = '/'){
		try{
			//$isSecure = Mage::app()->getStore()->isCurrentlySecure();
			$isSecure = false;
			$currentUrl = Mage::getUrl('',array('_secure'=>$isSecure));
			$parts = parse_url($currentUrl);
			
			$requestUrl = $parts['scheme'] . '://' . $parts['host'] . $ee_path . str_replace(" ", "%20", $template);
			//return $requestUrl;
			$result =  $this->getContent($requestUrl);
			if(!$result || $result == ''){
				throw new Exception('Bad or empty response');
			} else {
				return $result;
			}
		} catch (Exception $e) {
			$url='http://lunddev.local.com' . $ee_path . str_replace(" ", "%20", $template);
			return $this->getContent($url);
		}
	}

	protected function getContent($url){
		$curl = Mage::helper('ulintegratee/curl');
		$curl->setOption(CURLOPT_TIMEOUT,10);
		$curl->setOption(CURLOPT_CONNECTTIMEOUT,10);
		$response = $curl->call($url,Zend_Http_Client::GET);
		return $response;
	}
}
