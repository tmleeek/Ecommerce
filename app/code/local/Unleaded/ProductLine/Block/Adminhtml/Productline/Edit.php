<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    public function __construct() 
    {
        parent::__construct();
        
        $this->_objectId   = 'id';
        $this->_blockGroup = 'unleaded_productline';
        $this->_controller = 'adminhtml_productline';
        $this->_mode       = 'edit';

        $this->_updateButton('save', 'label', Mage::helper('unleaded_productline')->__('Save Product Line'));
        $this->_updateButton('delete', 'label', Mage::helper('unleaded_productline')->__('Delete Product Line'));

        $this->_addButton('saveandcontinue', [
            'label'   => Mage::helper('unleaded_productline')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save',
        ], -100);

        $this->_formScripts[] = '
            function saveAndContinueEdit() {
                editForm.submit($("edit_form").action + "back/edit/");
            }
        ';
    }

    public function getHeaderText() 
    {
        if (Mage::registry('productline_data') && Mage::registry('productline_data')->getId()) {
            $header = 'Edit Product Line ' . $this->htmlEscape(Mage::registry('productline_data')->getName());
            return Mage::helper('unleaded_productline')->__($header);
        } else {
            return Mage::helper('unleaded_productline')->__('Add Product Line');
        }
    }
}