<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Vidtest_Adminhtml_Aw_Vidtest_AuthsubController extends Mage_Adminhtml_Controller_Action {

    public function authAction() {
        if ($apiModelCode = $this->getRequest()->getParam('api_model_code')) {
            $apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($apiModelCode);
            $requestUrl = $apiModel->getHttpClient()->createAuthUrl();
        }
        $this->getResponse()->setRedirect($requestUrl);
    }

    public function returnAction() {
        if ($code = $this->getRequest()->getParam('code')) {
            $apiModelCode = $this->getRequest()->getParam('api_model_code');
            $apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($apiModelCode);
            $apiModel->getHttpClient()->authenticate($code);

            $access_token = $apiModel->getHttpClient()->getAccessToken();
            $refresh_token = $apiModel->getHttpClient()->getRefreshToken();

            Mage::getConfig()->saveConfig('vidtest/youtube/access_token', $access_token);
            Mage::getConfig()->saveConfig('vidtest/youtube/refresh_token', $refresh_token);
        }

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'vidtest'));
    }

    public function revokeAction() {
        if ($apiModelCode = $this->getRequest()->getParam('api_model_code')) {
            $apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($apiModelCode);
            if (($connector = Mage::getSingleton('vidtest/connector'))
                    && ($apiModel = $connector->getApiModel($apiModelCode))
                    && $apiModel->logOut()) {
                Mage::getConfig()->saveConfig('vidtest/youtube/access_token', '');
                Mage::getConfig()->saveConfig('vidtest/youtube/refresh_token', '');
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vidtest')->__('Token revoked successfull'));
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('vidtest')->__('Token revoking was failed'));
            }
        }
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'vidtest'));
    }

}