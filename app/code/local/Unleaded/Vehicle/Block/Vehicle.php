<?php

class Unleaded_Vehicle_Block_Vehicle extends Mage_Core_Block_Template {

    public function getGarageVehicles() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getGarage();
        } else {
            $guestUnique = Mage::getSingleton('core/cookie')->get('guestUnique');

            $guestGarageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
            $guestGarageModel->addFieldToFilter('customer_id', $guestUnique);

            return json_decode($guestGarageModel->getFirstItem()->getVehicles());
        }
    }

    public function getSelectedVehicle() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getSelectedVehicle();
        } else {
            $guestUnique = Mage::getSingleton('core/cookie')->get('guestUnique');
            $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
            $garageModel->addFieldToFilter('customer_id', $guestUnique);

            return $garageModel->getFirstItem()->getSelectedVehicle();
        }
    }

    public function getSearchQuery($vehicle) {

        $searchUrl = Mage::getBaseUrl() . "models/";
        $searchUrl .= $vehicle->getYear() . "-";
        $searchUrl .= strtolower($vehicle->getMake()). "-";
        $searchUrl .= str_replace(" ", "_", strtolower($vehicle->getModel()));

        return $searchUrl;
    }

}
