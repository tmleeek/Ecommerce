<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline 
	extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    public function __construct() 
    {
        $this->_controller = "adminhtml_productline";
        $this->_blockGroup = "unleaded_productline";
        $this->_headerText = Mage::helper("unleaded_productline")->__("Product Line Manager");
        $this->_addButtonLabel = Mage::helper("unleaded_productline")->__("Add New Product Line");
        parent::__construct();
    }
}