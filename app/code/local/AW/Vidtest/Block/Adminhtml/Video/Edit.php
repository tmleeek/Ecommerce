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
 * Video Testimonial Edit Form Container
 */
class AW_Vidtest_Block_Adminhtml_Video_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * Class constructor/
     */
    public function __construct() {
        parent::__construct();

        $id = $this->getRequest()->getParam('id');
        $this->_objectId = 'id';
        $this->_blockGroup = 'vidtest';
        $this->_controller = 'adminhtml_video';

        $this->_updateButton('save', 'label', Mage::helper('vidtest')->__('Save Video'));
        $this->_updateButton('delete', 'label', Mage::helper('vidtest')->__('Delete Testimonial'));

        if ($id) {
            $this->_addButton('fulldelete', array(
                'label' => Mage::helper('vidtest')->__('Delete Testimonial and Source'),
                'onclick' => 'fulldelete()',
                'class' => 'delete',
                    ), 0);
        } else {
            $this->_removeButton('delete');
        }

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $params = array('id' => $id);
        # Say about responser
        $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_EDIT;
        # Set back route if need it
        if ($this->_isPending()) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE;
        }
        if ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT;
            $params['product_id'] = $this->getRequest()->getParam('product_id');
            $params['store'] = $this->getRequest()->getParam('store');
        }
        $fulldeleteUrl = $this->getUrl('*/*/fulldelete', $params);
        $isFile = AW_Vidtest_Model_System_Config_Source_Video_Sourcetype::SOURCE_FILE;
        $isLink = AW_Vidtest_Model_System_Config_Source_Video_Sourcetype::SOURCE_LINK;
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function fulldelete(){
                deleteConfirm('Are you sure you want to do this?', '{$fulldeleteUrl}');
            }
            function selectSourceType(){
                var is_file = '{$isFile}';
                var is_link = '{$isLink}';
                var req_class = 'required-entry';
                var val_failed = 'validation-failed';
                while ($('video_file_field').hasClassName(req_class)){
                    $('video_file_field').removeClassName(req_class);
                }
                $('video_file_container').style.display = 'none';
                while ($('video_link_field').hasClassName(req_class)){
                    $('video_link_field').removeClassName(req_class);
                }
                $('video_link_container').style.display = 'none';
                if ($('source_type').value == is_file){
                    $('video_file_field').addClassName(req_class);
                    $('video_file_container').style.display = 'block';
                } else {
                    $('video_link_field').addClassName(req_class);
                    $('video_link_container').style.display = 'block';
                }
                if ($('advice-required-entry-video_link_field')){ $('advice-required-entry-video_link_field').remove(); }
                if ($('advice-required-entry-video_file_field')){ $('advice-required-entry-video_file_field').remove(); }

            }" . ((!(Mage::registry('video_data') && Mage::registry('video_data')->getId())) ? "
                selectSourceType();" : "");
    }

    /**
     * Retrives "Edit from Pending Grid" flag
     * @return boolean
     */
    protected function _isPending() {
        return ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY)
                == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE);
    }

    /**
     * Retrives Header text
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('video_data') && Mage::registry('video_data')->getId()) {
            return Mage::helper('vidtest')->__('Edit Video');
        } else {
            return Mage::helper('vidtest')->__('Add Video');
        }
    }

    /**
     * Get URL for back (reset) button
     * @return string
     */
    public function getBackUrl() {
        if ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params = array(
                'id' => $this->getRequest()->getParam('product_id'),
                'tab' => 'product_info_tabs_vidtest',
            );
            return $this->getUrl('adminhtml/catalog_product/edit', $params);
        } elseif ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE) {
            return $this->getUrl('*/*/pending');
        }
        return $this->getUrl('*/*/');
    }

    /**
     * Get URL to delete entry
     * @return string
     */
    public function getDeleteUrl() {
        $params = array($this->_objectId => $this->getRequest()->getParam($this->_objectId));
        if ($this->_isPending()) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE;
        }
        $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_EDIT;
        if ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT;
            $params['product_id'] = $this->getRequest()->getParam('product_id');
            $params['store'] = $this->getRequest()->getParam('store');
        }
        return $this->getUrl('*/*/delete', $params);
    }

}