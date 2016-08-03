<?php

class Unleaded_Vehicle_Block_Vehicle extends Mage_Core_Block_Template {

    public function getGarageVehicles() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getGarage();
        } else {
            $remoteAddr = Mage::helper('core/http')->getRemoteAddr();

            $guestGarageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
            $guestGarageModel->addFieldToFilter('customer_id', $remoteAddr);

            return json_decode($guestGarageModel->getFirstItem()->getVehicles());
        }
    }

    public function getSelectedVehicle() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getSelectedVehicle();
        } else {
            $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
            $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
            $garageModel->addFieldToFilter('customer_id', $remoteAddr);

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
