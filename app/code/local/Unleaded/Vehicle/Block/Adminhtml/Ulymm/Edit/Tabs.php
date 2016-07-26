<?php

class Unleaded_Vehicle_Block_Adminhtml_Ulymm_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("ulymm_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("vehicle")->__("Vehicle Information"));
    }

    protected function _beforeToHtml() {
        $this->addTab("form_section", array(
            "label" => Mage::helper("vehicle")->__("Vehicle Information"),
            "title" => Mage::helper("vehicle")->__("Vehicle Information"),
            "content" => $this->getLayout()->createBlock("vehicle/adminhtml_ulymm_edit_tab_form")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
