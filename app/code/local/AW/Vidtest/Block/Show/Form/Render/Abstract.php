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
 * Abstract Upload Form Class
 */
class AW_Vidtest_Block_Show_Form_Render_Abstract extends Mage_Core_Block_Template {

    protected $_apiKey = 'abstract';

    /**
     * Rertives antibot secret key for upload form
     * @param int $count
     * @return string
     */
    protected function _getNewSecretKey($count = 8) {
        $pattern = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789_=";
        $str = '';
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $str .= $pattern{mt_rand(0, strlen($pattern) - 1)};
            }
        }
        return $str;
    }

    /**
     * Retrives secret key for a form
     * @return string
     */
    public function getSecretKey() {
        $helper = Mage::helper('vidtest');
        if (!$helper->getValue($this->_apiKey . '_secret_key')) {
            $helper->setValue($this->_apiKey . '_secret_key', $this->_getNewSecretKey(32));
        }
        return $helper->getValue($this->_apiKey . '_secret_key');
    }

    /**
     * retrives Customer Name
     * @return string
     */
    public function getCustomerName() {
        if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
            return $customer->getName();
        }
        return null;
    }

    /**
     * retrives Customer Email
     * @return string
     */
    public function getCustomerEmail() {
        if ($customer = Mage::getSingleton('customer/session')->getCustomer()) {
            return $customer->getEmail();
        }
        return null;
    }

    /**
     * Retrives current product id
     * @return int
     */
    public function getProductId() {
        if ($product = Mage::registry('current_product')) {
            return $product->getId();
        }
        return null;
    }

}
