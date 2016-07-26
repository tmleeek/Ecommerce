<?php
class Magestore_Sociallogin_Model_Ljlogin extends Mage_Core_Model_Abstract {
    
	public function newMy(){
		try{
			require_once Mage::getBaseDir('base').DS.'lib'.DS.'OpenId'.DS.'openid.php';
		}catch(Exception $e){}
		$openid = new LightOpenID(Mage::app()->getStore()->getUrl());       
		return $openid;
	}	
	
	public function getLjLoginUrl($identity){
		$my_id = $this->newMy();
        $my = $this->setLjIdlogin($my_id,$identity);
		$loginUrl = $my->authUrl();
		return $loginUrl;
	}
	
	public function setLjIdlogin($openid,$identity){
        $openid->identity = "http://".$identity.".livejournal.com";
        $openid->required = array(
        'namePerson/first',
        'namePerson/last',
        'namePerson/friendly',
        'contact/email'
        );
        $openid->returnUrl = Mage::app()->getStore()->getUrl('sociallogin/ljlogin/login');
		return $openid;
    }
}
  
