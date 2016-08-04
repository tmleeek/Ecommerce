<?php

class Unleaded_Vehicle_Model_Observer {

    public function performEssentials($observer) {
        if (!Mage::getSingleton('customer/session')->isLoggedIn() && !Mage::getSingleton('core/cookie')->get('guestUnique')){
            $cookie = Mage::getSingleton('core/cookie');
            $guestUnique = uniqid();
            $cookie->set(
                    'guestUnique', 
                    $guestUnique,
                    (60 * 60 * 24 * 30), 
                    '/'
            );
        }
        
        $brand = Mage::app()->getRequest()->getParam('brand');

        switch ($brand) {

            case "lund": {
                  Mage::app()->setCurrentStore('lund');
                  break;
            }
            case "avs": {
                Mage::app()->setCurrentStore('avs');
                break;
            }
            default: {
                Mage::app()->setCurrentStore('default');
                break;
            }
       }
    }

}
