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
 * Video Testimonials Product Edit Tab
 */
class AW_Vidtest_Block_Adminhtml_Catalog_Product_Edit_Tab_Vidtest extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    /**
     * Video Testimonials product edit tab template
     */
    const VIDTEST_TEMPLATE = "aw_vidtest/product/edit/tab.phtml";

    /**
     * Cached current product
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * This is constructor
     * It is set up template
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate(self::VIDTEST_TEMPLATE);
    }

    /**
     * Returns tab label
     * @return String
     */
    public function getTabLabel() {
        return Mage::helper('vidtest')->__('Video Testimonials');
    }

    /**
     * Returns tab title
     * @return String
     */
    public function getTabTitle() {
        return Mage::helper('vidtest')->__('Video Testimonials');
    }

    /**
     * Check if tab can be displayed
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Check if tab is hidden
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

    /**
     * Render block HTML
     * @return String
     */
    protected function _toHtml() {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
                ->setId('vidtestInfo');

        $accordion->addItem('general', array(
            'title' => Mage::helper('vidtest')->__('General'),
            'content' => $this->getLayout()->createBlock('vidtest/adminhtml_catalog_product_edit_tab_vidtest_general')->toHtml(),
            'open' => true,
        ));

        $accordion->addItem('statistics', array(
            'title' => Mage::helper('vidtest')->__('Product Videos'),
            'content' => $this->getLayout()->createBlock('vidtest/adminhtml_catalog_product_edit_tab_vidtest_video')->toHtml(),
            'open' => true,
        ));

        $this->setChild('accordion', $accordion);

        return parent::_toHtml();
    }

}