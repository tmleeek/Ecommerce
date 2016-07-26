<?php
class Magestore_Sociallogin_TwloginController extends Mage_Core_Controller_Front_Action{
	// url to login
	
    public function loginAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){return;}
		if (!$this->getAuthorizedToken()) {
			$token = $this->getAuthorization();
		}
		else {
			$token = $this->getAuthorizedToken();
		}
		
        return $token;
    }
	
	//url after authorize
	public function userAction() {
		$otwitter = Mage::getModel('sociallogin/twlogin');
		$requestToken = Mage::getSingleton('core/session')->getRequestToken();
		
		$oauth_data = array(
                'oauth_token' => $this->getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->getRequest()->getParam('oauth_verifier')
         );
		// fixed by Hai Ta 
		try{
			 $token = $otwitter->getAccessToken($oauth_data, unserialize($requestToken));
		}catch(Exception $e){
			Mage::getSingleton('core/session')->addError($this->__('Login failed as you have not granted access.'));			
			die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"".Mage::app()->getStore()->getBaseUrl()."\"} window.close();</script>");
		}
       	//end fixed	
		$params = array(
			'consumerKey'=> Mage::helper('sociallogin')->getTwConsumerKey(), 
			'consumerSecret'=>Mage::helper('sociallogin')->getTwConsumerSecret(), 
			'accessToken'=>$token,
		);
		// $twitter = new Zend_Service_Twitter($params);
		// $twitter = new Magestore_Sociallogin_Model_Twitter($params);
		// $response = $twitter->userShow($token->user_id);
		// $twitterId = (string)$response->id;// get twitter account ID		
		$twitterId = $token->user_id;// get twitter account ID				
		$customerId = $this->getCustomerId($twitterId);
		
		if($customerId){ //login
			$customer = Mage::getModel('customer/customer')->load($customerId);
				// fix confirmation
			if ($customer->getConfirmation())
			{
				try {
					$customer->setConfirmation(null);
					$customer->save();
				}catch (Exception $e) {
				}
	  		}
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
			die("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"".$this->_loginPostRedirect()."\";}else{try{window.opener.location.href=\"".$this->_loginPostRedirect()."\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");
			
		}else{	// redirect to login page
			$name = (string)$token->screen_name;		
			$email = $name . '@twitter.com';
			$user['firstname'] = $name;
			$user['lastname'] = $name;			
			$user['email'] = $email;
			//get website_id and sote_id of each stores
			$store_id = Mage::app()->getStore()->getStoreId();
			$website_id = Mage::app()->getStore()->getWebsiteId();
			$customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);//add edtition	
			if(!$customer || !$customer->getId()){
				//Login multisite
				$customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($user, $website_id, $store_id );
			}	
				// fix confirmation
			if ($customer->getConfirmation())
			{
				try {
					$customer->setConfirmation(null);
					$customer->save();
				}catch (Exception $e) {
				}
	  		}	
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);							
			$this->setAuthorCustomer($twitterId, $customer->getId());	
			Mage::getSingleton('core/session')->setCustomerIdSocialLogin($twitterId);						
			if (Mage::getStoreConfig('sociallogin/mplogin/is_send_password_to_customer')){
				$customer->sendPasswordReminderEmail();
			}			
			$nextUrl = Mage::helper('sociallogin')->getEditUrl();	
			Mage::getSingleton('core/session')->addNotice($this->__('Please enter your contact detail.'));			
			die("<script>window.close();window.opener.location = '$nextUrl';</script>");
		}
			
    }
	
	//get customer id from twitter account if user connected
	public function getCustomerId($twitterId){
		$user = Mage::getModel('sociallogin/customer')->getCollection()
						->addFieldToFilter('twitter_id', $twitterId)
						->getFirstItem();
		if($user)
			return $user->getCustomerId();
		else
			return NULL;
	}
	
	// if exit access token
	public function getAuthorizedToken() {
        $token = false;
        if (!is_null(Mage::getSingleton('core/session')->getAccessToken())) {
            $token = unserialize(Mage::getSingleton('core/session')->getAccessToken());
        }
        return $token;
    }
     
	// if not exit access token
    public function getAuthorization() {
        $otwitter = Mage::getModel('sociallogin/twlogin');		
        /* @var $otwitter Twitter_Model_Consumer */
        $otwitter->setCallbackUrl(Mage::app()->getStore()->getUrl('sociallogin/twlogin/user',array('_secure'=>true)));        
        if (!is_null($this->getRequest()->getParam('oauth_token')) && !is_null($this->getRequest()->getParam('oauth_verifier'))) {
            $oauth_data = array(
                'oauth_token' => $this->_getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->_getRequest()->getParam('oauth_verifier')
            );
            $token = $otwitter->getAccessToken($oauth_data, unserialize(Mage::getSingleton('core/session')->getRequestToken()));
            Mage::getSingleton('core/session')->setAccessToken(serialize($token));
            $otwitter->redirect();
        } else {
            $token = $otwitter->getRequestToken();
            Mage::getSingleton('core/session')->setRequestToken(serialize($token));
            $otwitter->redirect();
        }
        return $token;
    }	
	
	/**
	* input: 
	*	@mpId
	*	@customerid	
	**/
	public function setAuthorCustomer($twId, $customerId){
		$mod = Mage::getModel('sociallogin/customer');
		$mod->setData('twitter_id', $twId);		
		$mod->setData('customer_id', $customerId);		
		$mod->save();		
		return ;
	}
	
	/**
	* return @collectin in model customer
	**/
	public function getCustomer ($id){
		$collection = Mage::getModel('customer/customer')->load($id);
		return $collection;
	}
	protected function _loginPostRedirect()
    {
            $selecturl= Mage::getStoreConfig(('sociallogin/general/select_url'),Mage::app()->getStore()->getId());
	if($selecturl==0) return Mage::getUrl('customer/account');
	if($selecturl==2) return Mage::getUrl();
	if($selecturl==3) return Mage::getSingleton('core/session')->getSocialCurrentpage();
	if($selecturl==4) return Mage::getStoreConfig(('sociallogin/general/custom_page'),Mage::app()->getStore()->getId());
	if($selecturl==1 && Mage::helper('checkout/cart')->getItemsCount()!=0) return Mage::getUrl('checkout/cart');else return Mage::getUrl();
    }
}