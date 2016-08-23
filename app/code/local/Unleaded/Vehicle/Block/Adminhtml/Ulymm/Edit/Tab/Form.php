<?php

class Unleaded_Vehicle_Block_Adminhtml_Ulymm_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("vehicle_form", array("legend" => Mage::helper("vehicle")->__("Vehicle information")));


        $fieldset->addField("year", "select", array(
            "label" => Mage::helper("vehicle")->__("Year"),
            "values" => Mage::getModel('vehicle/source_year')->toOptionArray(),
            "class" => "required-entry",
            "required" => true,
            "name" => "year",
        ));

        $fieldset->addField("make", "select", array(
            "label" => Mage::helper("vehicle")->__("Make"),
            "values" => Mage::getModel('vehicle/source_make')->toOptionArray(),
            "class" => "required-entry",
            "required" => true,
            "name" => "make",
        ));

        $fieldset->addField("model", "select", array(
            "label" => Mage::helper("vehicle")->__("Model"),
            "values" => Mage::getModel('vehicle/source_model')->toOptionArray(),
            "class" => "required-entry",
            "required" => true,
            "name" => "model",
        ));
        
        $fieldset->addField("sub_model", "select", array(
            "label" => Mage::helper("vehicle")->__("Sub Model"),
            "values" => Mage::getModel('vehicle/source_submodel')->toOptionArray(),
            "class" => "required-entry",
            "required" => true,
            "name" => "sub_model",
        ));

        $fieldset->addField("sub_detail", "select", array(
            "label" => Mage::helper("vehicle")->__("Sub Detail"),
            "values" => Mage::getModel('vehicle/source_subdetail')->toOptionArray(),
            "class" => "required-entry",
            "required" => true,
            "name" => "sub_detail",
        ));
        
        if (Mage::getSingleton("adminhtml/session")->getUlymmData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getUlymmData());
            Mage::getSingleton("adminhtml/session")->setUlymmData(null);
        } elseif (Mage::registry("ulymm_data")) {
            $form->setValues(Mage::registry("ulymm_data")->getData());
        }
        return parent::_prepareForm();
    }

}
