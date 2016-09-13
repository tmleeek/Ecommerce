<?php

class Unleaded_Vehicle_Model_Source_Vehicle extends Mage_Eav_Model_Entity_Attribute_Source_Abstract 
{
    public function getAllOptions() 
    {
        if (is_null($this->_options)) {
            $vehiclesCollection = Mage::getModel('vehicle/ulymm')->getCollection()
                    ->addFieldToSelect('ymm_id')
                    ->addFieldToSelect(['year', 'make', 'model', 'sub_model', 'sub_detail']);
            if ($vehiclesCollection->count() >= 1) {
                foreach ($vehiclesCollection as $_vehicle) {
                    $this->_options[] = [
                        'label' => implode(' ', [
                            $_vehicle->getYear(),
                            $_vehicle->getMake(),
                            $_vehicle->getModel(),
                            $_vehicle->getSubModel(),
                            $_vehicle->getSubDetail(),
                        ]),
                        'value' => $_vehicle->getId(),
                    ];
                }
            }
        }
        return $this->_options;
    }

    public function toOptionArray() 
    {
        return $this->getAllOptions();
    }

    public function getShortenedOptionText($value)
    {
        $options = $this->getShortenedAllOptions();
        // Fixed for tax_class_id and custom_design
        if (sizeof($options) > 0) foreach($options as $option) {
            if (isset($option['value']) && $option['value'] == $value) {
                return isset($option['label']) ? $option['label'] : $option['value'];
            }
        } // End
        if (isset($options[$value])) {
            return $options[$value];
        }
        return false;
    }

    public function getShortenedAllOptions() 
    {
        if (is_null($this->_options)) {
            $vehiclesCollection = Mage::getModel('vehicle/ulymm')->getCollection()
                    ->addFieldToSelect('ymm_id')
                    ->addFieldToSelect(['year', 'make', 'model']);
            if ($vehiclesCollection->count() >= 1) {
                foreach ($vehiclesCollection as $_vehicle) {
                    $this->_options[] = [
                        'label' => implode(' ', [
                            $_vehicle->getYear(),
                            $_vehicle->getMake(),
                            $_vehicle->getModel()
                        ]),
                        'value' => $_vehicle->getId(),
                    ];
                }
            }
        }
        return $this->_options;
    }
}