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
 * Extra Downloads tab content General
 */
class AW_Vidtest_Block_Adminhtml_Catalog_Product_Edit_Tab_Vidtest_Video extends Mage_Adminhtml_Block_Template {
    /**
     * Template path
     */
    const TAB_GENERAL_TEMPLATE = "aw_vidtest/product/edit/tab/video.phtml";

    /**
     * This is constructor
     * Set General template
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate(self::TAB_GENERAL_TEMPLATE);
    }

    /**
     * Get model of the product that is being edited
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return Mage::registry('current_product');
    }

    /**
     * Retrives grid HTML code
     * @return string
     */
    public function getGridHtml() {
        return $this->getLayout()->createBlock('vidtest/adminhtml_catalog_product_edit_tab_vidtest_video_grid')->toHtml();
    }

}