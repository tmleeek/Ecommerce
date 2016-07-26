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
 * Show Product Link Element for backend area
 */
class AW_Vidtest_Model_Form_Element_Product extends Varien_Data_Form_Element_Text {

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml() {
        $product_id = Mage::app()->getRequest()->getParam('product_id');
        if ($product_id) {
            $product = Mage::getModel('catalog/product')->load($product_id);
            $productUrl = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $product_id));
            $selectorUrl = Mage::helper('adminhtml')->getUrl('vidtest_admin/admin_video/new');
//            $html = "<a href=\"{$productUrl}\">".$product->getName()."</a>";
            $html = $product->getName();
            $html .= " [" . "<a href=\"{$productUrl}\">" . Mage::helper('vidtest')->__('view') . "</a>" . "]";
            $html .= " [" . "<a href=\"{$selectorUrl}\">" . Mage::helper('vidtest')->__('change') . "</a>" . "]";
            $html .= "<input type=\"hidden\" value=\"{$product_id}\" name=\"product_id\" id=\"product_id\">";
            return $html;
        }
        return '';
    }

}