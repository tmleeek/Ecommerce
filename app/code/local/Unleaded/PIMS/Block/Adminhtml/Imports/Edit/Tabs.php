<?php

class Unleaded_PIMS_Block_Adminhtml_Imports_Edit_Tabs 
    extends Mage_Adminhtml_Block_Widget_Tabs 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('adminhtml_imports_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('unleaded_pims')->__('PIMS Imports'));
    }
}