<?php

class Unleaded_Vehicle_Model_Customer_Customer extends Mage_Customer_Model_Customer {

    public function getGarage() {
        $customerId = $this->getId();

        $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
        $garageModel->addFieldToFilter('customer_id', $customerId);

        $garageData = $garageModel->getFirstItem()->getData();
        return json_decode($garageData['vehicles']);
    }

    public function getSelectedVehicle() {
        $customerId = $this->getId();

        $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
        $garageModel->addFieldToFilter('customer_id', $customerId);

        return $garageModel->getFirstItem()->getSelectedVehicle();
    }

}
