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
 * Video Testimonial tab Video Grid
 */
class AW_Vidtest_Block_Adminhtml_Catalog_Product_Edit_Tab_Vidtest_Video_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Current product
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId('productVideoGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Retrives "is pending grid" flad
     * @return boolean
     */
    public function getIsPending() {
        if ($parent = $this->getParentBlock()) {
            return!!$parent->getPending();
        }
        return false;
    }

    /**
     * Set up product model to grid
     * @param Mage_Catalog_Model_Product $product
     * @return AW_Vidtest_Block_Adminhtml_Catalog_Product_Edit_Tab_Vidtest_Video_Grid
     */
    public function setProduct($product) {
        $this->_product = $product;
        return $this;
    }

    /**
     * Retrives current product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return $this->_product ? $this->_product : Mage::registry('current_product');
    }

    /**
     * Prepare collection to show in grid
     * @return AW_Vidtest_Block_Adminhtml_Video_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('vidtest/video')
                ->getCollection()
                ->addProductFilter($this->getProduct()->getId())
        ;

        if ($storeId = $this->getProduct()->getStoreId()) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns to show
     */
    protected function _prepareColumns() {
        $video = Mage::getModel('vidtest/video');

        $this->addColumn('video_id', array(
            'header' => $this->__('Id'),
            'align' => 'left',
            'index' => 'video_id',
            'width' => 50,
            'type' => 'number'
        ));

        if (!Mage::app()->isSingleStoreMode() && !$this->getProduct()->getStoreId()) {
            $this->addColumn('stores', array(
                'header' => $this->__('Store View'),
                'index' => 'stores',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'index' => 'title',
            'type' => 'text',
        ));

        $this->addColumn('author_name', array(
            'header' => $this->__('Author Name'),
            'index' => 'author_name',
            'type' => 'text',
        ));

        $this->addColumn('author_email', array(
            'header' => $this->__('Author Email'),
            'index' => 'author_email',
            'type' => 'text',
        ));

        $this->addColumn('rate', array(
            'header' => $this->__('Rate'),
            'index' => 'rate',
            'type' => 'number',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'width' => '160px',
            'index' => 'status',
            'type' => 'options',
            'options' => $video->getStatusesArray(),
        ));


        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'width' => '80',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array('base' => 'vidtest_admin/admin_video/edit'),
                    'field' => 'id',
                ),
                array(
                    'caption' => $this->__('Enable'),
                    'url' => array('base' => 'vidtest_admin/admin_video/enable'),
                    'field' => 'id',
                ),
                array(
                    'caption' => $this->__('Disable'),
                    'url' => array('base' => 'vidtest_admin/admin_video/disable'),
                    'field' => 'id',
                ),
                array(
                    'caption' => $this->__('Delete testimonial'),
                    'url' => array('base' => 'vidtest_admin/admin_video/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want to delete testimonial?'),
                ),
                array(
                    'caption' => $this->__('Delete testimonial and video source'),
                    'url' => array('base' => 'vidtest_admin/admin_video/fulldelete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want to delete testimonial and video source?'),
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('video_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('videos');

        $this->getMassactionBlock()->addItem('update_status', array(
            'label' => Mage::helper('vidtest')->__('Update status'),
            'url' => $this->getUrl('vidtest_admin/admin_video/massUpdateStatus'),
            'additional' => array(
                'status' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => Mage::getModel('vidtest/video')->getStatusesArray(),
            ))));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete testimonials'),
            'url' => $this->getUrl('vidtest_admin/admin_video/massDelete'),
            'confirm' => $this->__('Are you sure you want to delete testimonials?')
        ));

        $this->getMassactionBlock()->addItem('fulldelete', array(
            'label' => $this->__('Delete testimonials and video sources'),
            'url' => $this->getUrl('vidtest_admin/admin_video/massFulldelete'),
            'confirm' => $this->__('Are you sure you want to delete testimonials and video sources?')
        ));
    }

    /**
     * Add store filter to collection
     * @param AW_Vidtest_Model_Mysql4_Video_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return null
     */
    protected function _filterStoreCondition($collection, $column) {
        if (!$value = $column->getFilter()->getValue())
            return;
        $collection->addStoreFilter($value);
    }

    /**
     * Url to edit row
     * @return string
     */
    public function getRowUrl($row) {
        $params = array(
            'id' => $row->getId(),
            AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY => AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT,
            'product_id' => $this->getProduct()->getId(),
            'store' => $this->getRequest()->getParam('store', 0),
        );
        return $this->getUrl('vidtest_admin/admin_video/edit', $params);
    }

    /**
     * Retrives grid url to get content
     * @return string
     */
    public function getGridUrl() {
        $params = array(
            'product_id' => $this->getProduct()->getId(),
            'store' => $this->getRequest()->getParam('store', 0),
        );
        return $this->getUrl('vidtest_admin/admin_product/grid', $params);
    }

}