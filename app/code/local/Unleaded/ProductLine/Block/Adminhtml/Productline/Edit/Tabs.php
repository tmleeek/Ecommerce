<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline_Edit_Tabs 
    extends Mage_Adminhtml_Block_Widget_Tabs 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId("productline_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("unleaded_productline")->__("Product Line Information"));
    }

    protected function _beforeToHtml() 
    {
        $this->addTab("form_section", [
            "label"   => Mage::helper("unleaded_productline")->__("Product Line Information"),
            "title"   => Mage::helper("unleaded_productline")->__("Product Line Information"),
            "content" => $this->getLayout()->createBlock("unleaded_productline/adminhtml_productline_edit_tab_form")->toHtml(),
        ]);
        return parent::_beforeToHtml();
    }
}