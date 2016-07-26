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
 * Product Selector Grid Container
 */
class AW_Vidtest_Block_Adminhtml_Product_Selector extends Mage_Adminhtml_Block_Catalog_Product {

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('widget/grid/container.phtml');
        $this->_controller = 'adminhtml_product_selector';
        $this->_blockGroup = 'vidtest';
        $this->_removeButton('add_new');
    }

    /**
     * Prepare button and grid
     * @return AW_Vidtest_Block_Adminhtml_Product_Selector
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->_removeButton('add_new');
        $this->unsetChild('grid');
        $this->setChild('grid', $this->getLayout()->createBlock('vidtest/adminhtml_product_selector_grid', 'aw.selector.product.grid'));
    }

    /**
     * Retrives Header text
     * @return string
     */
    public function getHeaderText() {
        return Mage::helper('vidtest')->__('Add Video');
    }

}