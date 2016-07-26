<?php
class Magestore_Sociallogin_Model_Wplogin extends Mage_Core_Model_Abstract {
    
	public function newWp(){	
		try{
			require_once Mage::getBaseDir('base').DS.'lib'.DS.'OpenId'.DS.'openid.php';
		}catch(Exception $e){}
		
		$openid = new LightOpenID(Mage::app()->getStore()->getUrl());       
		return $openid;
	}		
	
	public function getWpLoginUrl($name_blog){
		$wp_id = $this->newWp();
        $wp = $this->setWpIdlogin($wp_id, $name_blog);		
        try{
            $loginUrl = $wp->authUrl();
            return $loginUrl;            
        }  catch (Exception $e){
            return null;
        }		
	}
    
    public function setWpIdlogin($openid, $name_blog){
        
        $openid->identity = 'http://'. $name_blog . '.wordpress.com';
        $openid->required = array(
        'namePerson/first',
        'namePerson/last',
        'namePerson/friendly',
        'contact/email',
        );
        
        $openid->returnUrl = Mage::app()->getStore()->getUrl('sociallogin/wplogin/login');
		return $openid;
    }      
}
  
