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
        
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('vehicle')->__('Vehicle Description'),
            'name' => 'description',
        ));
        
        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('vehicle')->__('Vehicle Image'),
            'name' => 'image',
            'note' => '(*.jpg, *.png, *.gif)',
        ));
        
        $fieldset->addField("trim", "text", array(
            "label" => Mage::helper("vehicle")->__("Trim"),
            "name" => "trim",
        ));

        $fieldset->addField("type", "text", array(
            "label" => Mage::helper("vehicle")->__("Type"),
            "name" => "type",
        ));

        $fieldset->addField("sub_model", "text", array(
            "label" => Mage::helper("vehicle")->__("Sub Model"),
            "name" => "sub_model",
        ));
        
        $fieldset->addField("body_style", "text", array(
            "label" => Mage::helper("vehicle")->__("Body Style"),
            "name" => "body_style",
        ));
        
        $fieldset->addField("bed_length", "text", array(
            "label" => Mage::helper("vehicle")->__("Bed Length"),
            "name" => "bed_length",
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
