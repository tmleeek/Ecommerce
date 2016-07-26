<?php

class Unleaded_Vehicle_Model_Source_Vehicle extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    const MAIN = 1;
    const OTHER = 2;

    public function getAllOptions() {
        if (is_null($this->_options)) {
            $vehiclesCollection = Mage::getModel('vehicle/ulymm')->getCollection()
                    ->addFieldToSelect('ymm_id')
                    ->addFieldToSelect('year')
                    ->addFieldToSelect('make')
                    ->addFieldToSelect('model');
            if ($vehiclesCollection->count() >= 1) {
                foreach ($vehiclesCollection as $_vehicle) {
                    $this->_options[] = [
                        'label' => $_vehicle->getYear() . " " . $_vehicle->getMake() . " " . $_vehicle->getModel(),
                        'value' => $_vehicle->getId(),
                    ];
                }
            }
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}
