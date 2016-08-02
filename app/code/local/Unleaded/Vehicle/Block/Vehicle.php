<?php

class Unleaded_Vehicle_Block_Vehicle extends Mage_Core_Block_Template {

    public function getGarageVehicles() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getGarage();
        } else {
            return [];
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

        $yearId = Mage::helper('vehicle')->mapAttributeOptionLabelToId('year', $vehicle->getYear());
        $makeId = Mage::helper('vehicle')->mapAttributeOptionLabelToId('make', $vehicle->getMake());
        $modelId = Mage::helper('vehicle')->mapAttributeOptionLabelToId('model', $vehicle->getModel());

        $searchUrl = Mage::getUrl('ulvehicle/results/for') . "?";
        $searchUrl .= "year=" . $yearId . "&";
        $searchUrl .= "make=" . $makeId . "&";
        $searchUrl .= "model=" . $modelId;

        return $searchUrl;
    }

}
