<?php

class Unleaded_PIMS_Block_Adminhtml_Events
	extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    public function __construct() 
    {
        $this->_controller = "adminhtml_events";
        $this->_blockGroup = "unleaded_pims";
        $this->_headerText = Mage::helper("unleaded_pims")->__("PIMS Events");
        parent::__construct();
    }
}