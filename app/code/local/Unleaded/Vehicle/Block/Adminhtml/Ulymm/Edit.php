<?php

class Unleaded_Vehicle_Block_Adminhtml_Ulymm_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {

        parent::__construct();
        $this->_objectId = "ymm_id";
        $this->_blockGroup = "vehicle";
        $this->_controller = "adminhtml_ulymm";
        $this->_updateButton("save", "label", Mage::helper("vehicle")->__("Save Vehicle"));
        $this->_updateButton("delete", "label", Mage::helper("vehicle")->__("Delete Vehicle"));

        $this->_addButton("saveandcontinue", array(
            "label" => Mage::helper("vehicle")->__("Save And Continue Edit"),
            "onclick" => "saveAndContinueEdit()",
            "class" => "save",
                ), -100);



        $this->_formScripts[] = "
                                function saveAndContinueEdit(){
                                        editForm.submit($('edit_form').action+'back/edit/');
                                }
                        ";
    }

    public function getHeaderText() {
        if (Mage::registry("ulymm_data") && Mage::registry("ulymm_data")->getId()) {
            return Mage::helper("vehicle")->__("Edit Vehicle '%s'", $this->htmlEscape(Mage::registry("ulymm_data")->getYear() . " " . Mage::registry("ulymm_data")->getMake() . " " . Mage::registry("ulymm_data")->getModel()));
        } else {
            return Mage::helper("vehicle")->__("Add Vehicle");
        }
    }

}
