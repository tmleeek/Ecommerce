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
 * Product Selector Grid
 */
class AW_Vidtest_Block_Adminhtml_Product_Selector_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid {

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId('selectProductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('aw_vidtest_product_filter');
    }

    /**
     * Disable any massaction
     * @return AW_Vidtest_Block_Adminhtml_Product_Selector_Grid
     */
    protected function _prepareMassaction() {
        return $this;
    }

    /**
     * Disable RSS and Action column
     * @return AW_Vidtest_Block_Adminhtml_Product_Selector_Grid
     */
    protected function _prepareColumns() {
        parent::_prepareColumns();
        if (isset($this->_columns['action'])) {
            unset($this->_columns['action']);
        }
        $this->_rssLists = array();
        return $this;
    }

    /**
     * Retrives row edit url, that forward us to add video
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/add', array(
                    'store' => $this->getRequest()->getParam('store'),
                    'product_id' => $row->getId())
        );
    }

    /**
     * Retrives grid url for ajax requests
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}