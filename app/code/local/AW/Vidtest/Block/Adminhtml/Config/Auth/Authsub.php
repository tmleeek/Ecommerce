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


/**
 * Open Id Authentification Config Block
 */
class AW_Vidtest_Block_Adminhtml_Config_Auth_Authsub extends Mage_Core_Block_Template {
    const TEMPLATE = 'aw_vidtest/config/auth/authsub.phtml';

    protected function _construct() {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE);
    }

    protected function _toHtml() {
        if ($this->getApiModelCode()) {
            return parent::_toHtml();
        }
    }

    /**
     * Retrives auth state for this store
     * @return boolean
     */
    public function isAuth() {
        $connector = Mage::getSingleton('vidtest/connector')->getApiModel($this->getApiModelCode());
        return $connector->isLoggedIn();
    }

    /**
     * Retrives Url to get OAuth Access
     * @return string
     */
    public function getAccessTokenUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/aw_vidtest_authsub/auth', array('api_model_code' => $this->getApiModelCode()));
    }

    /**
     * Retrives Url to get OAuth Access
     * @return string
     */
    public function getRevokeAccessTokenUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/aw_vidtest_authsub/revoke', array('api_model_code' => $this->getApiModelCode()));
    }

}