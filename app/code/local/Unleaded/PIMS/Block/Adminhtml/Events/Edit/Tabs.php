<?php

class Unleaded_PIMS_Block_Adminhtml_Events_Edit_Tabs 
    extends Mage_Adminhtml_Block_Widget_Tabs 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId("pims_events_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("unleaded_pims")->__("PIMS Events"));
    }

    protected function _beforeToHtml() 
    {
        $this->addTab("form_section", [
            "label"   => Mage::helper("unleaded_pims")->__("Event Information"),
            "title"   => Mage::helper("unleaded_pims")->__("Event Information")
        ]);
        return parent::_beforeToHtml();
    }
}