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
 * Open Id Authentification Config Block Renderer
 */
class AW_Vidtest_Block_Adminhtml_Config_Form_Element_Authsub extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Retrives Api Model Code
     * @param string $id
     * @return string
     */
    protected function _getApiModelCode($id) {
        if (is_string($id)) {
            $arr = explode("_", $id);
            if (isset($arr[1])) {
                return $arr[1];
            }
        }
        return null;
    }

    /**
     * Retrives element Html
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $route = $this->_getApiModelCode($element->getId());
        $html = '';
        if ($route) {
            $html = Mage::app()->getLayout()->createBlock('vidtest/adminhtml_config_auth_authsub')->setApiModelCode($route)->toHtml();
        }
        return parent::_getElementHtml($element) . $html;
    }

}
