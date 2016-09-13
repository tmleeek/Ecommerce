<?php

class Unleaded_Vehicle_Model_Customer_Observer {

    public function vehicleCarryForward($observer) {
        $customer = $observer->getCustomer();

        $vehicleKey = Mage::getSingleton('core/cookie')->get('currentVehicle');
        $guestKey = Mage::getSingleton('core/cookie')->get('guestUnique');

        if ($vehicleKey && $vehicleKey != "") {
            $this->_setVehicleToCustomerByKey($vehicleKey, $customer->getId());
        } elseif ($guestKey && $guestKey != "") {

            $vehicleId = $this->_getVehicleIdOfGuest($guestKey);
            if ($vehicleId && $vehicleId != "") {
                $this->_setVehicleToCustomer($vehicleId, $customer->getId());
            }
        }
        
        $this->_clearGuestVehicleSelection($guestKey);
    }

    protected function _setVehicleToCustomerByKey($key, $customerId) {
        $baseElements = explode("-", $key);

        $year = $baseElements[0];
        $make = ucwords(str_replace("_", " ", $baseElements[1]));
        $model = ucwords(str_replace("_", " ", $baseElements[2]));

        $vehicleId = Mage::getModel("vehicle/ulymm")
                ->getCollection()
                ->addFieldToFilter('year', $year)
                ->addFieldToFilter('make', $make)
                ->addFieldToFilter('model', $model)
                ->getFirstItem()
                ->getId();

        $this->_setVehicleToCustomer($vehicleId, $customerId);
    }

    protected function _setVehicleToCustomer($vehicleId, $customerId) {
        $garageModel = Mage::getModel('vehicle/ulgarage')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId);

        if ($garageModel->count() == 1) {
            $this->_addVehicleToGarage($garageModel, $vehicleId);
        } else {
            $newGarage = Mage::getModel('vehicle/ulgarage');
            $newGarage->setCustomerId($customerId);
            $newGarage->setVehicles(json_encode([$vehicleId]));
            $newGarage->setSelectedVehicle($vehicleId);
            $newGarage->save();
        }
    }

    protected function _addVehicleToGarage($garageModel, $vehicleId) {
        $garageData = $garageModel->getFirstItem();
        $garage = json_decode($garageData->getVehicles());
        if (!in_array($vehicleId, $garage)) {
            $garage[] = $vehicleId;
            $garageData->setVehicles(json_encode(array_values($garage)));
            $garageData->setSelectedVehicle($vehicleId);
            $garageData->save();
        } else {
            $garageData->setSelectedVehicle($vehicleId);
            $garageData->save();
        }
    }

    protected function _getVehicleIdOfGuest($guestKey) {
        return Mage::getModel('vehicle/ulgarage')->getCollection()->addFieldToFilter('customer_id', $guestKey)->getFirstItem()->getSelectedVehicle();
    }

    protected function _clearGuestVehicleSelection($guestKey) {
        $garageId = Mage::getModel('vehicle/ulgarage')->getCollection()->addFieldToFilter('customer_id', $guestKey)->getFirstItem()->getId();
        Mage::getModel('vehicle/ulgarage')->setId($garageId)->delete();
    }
}
