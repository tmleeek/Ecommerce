<?php

class Unleaded_PIMS_Block_Adminhtml_Messages
	extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    public function __construct() 
    {
        $this->_controller = "adminhtml_messages";
        $this->_blockGroup = "unleaded_pims";
        $this->_headerText = Mage::helper("unleaded_pims")->__("PIMS Messages");
        parent::__construct();
    }
}