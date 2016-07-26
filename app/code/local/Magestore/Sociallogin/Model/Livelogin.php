<?php
class Magestore_Sociallogin_Model_Livelogin extends Mage_Core_Model_Abstract {      
    public function newLive(){
        
		try{			
            require_once Mage::getBaseDir('base').DS.'lib'.DS.'Author'.DS.'OAuth2Client.php';
		}catch(Exception $e){}
        try{
            $live = new OAuth2Client(
                    Mage::helper('sociallogin')->getLiveAppkey(),                  
                    Mage::helper('sociallogin')->getLiveAppSecret(),
                    Mage::helper('sociallogin')->getAuthUrlLive()
                    );    
			$live->api_base_url     = "https://apis.live.net/v5.0/";
			$live->authorize_url    = "https://login.live.com/oauth20_authorize.srf";
			$live->token_url        = "https://login.live.com/oauth20_token.srf";			
			$live->out 			    = "https://login.live.com/oauth20_logout.srf";	
            return $live;
        }catch(Exception $e){}
    }
    
    public function getUrlAuthorCode(){
        $live = $this->newLive();
        return $live->authorizeUrl();
    }
}

  
