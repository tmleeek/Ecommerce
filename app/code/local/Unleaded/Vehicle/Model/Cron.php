<?php

class Unleaded_Vehicle_Model_Cron {

    public function clearGuestGarages() {
        $customers = Mage::getModel("customer/customer")->getCollection()->addAttributeToSelect('id');
        $customerIds = [];
        foreach ($customers as $customer) {
            $customerIds[] = $customer->getId();
        }

        $garageCollection = Mage::getModel('vehicle/ulgarage')->getCollection();

        foreach ($garageCollection as $garage) {
            if (!in_array($garage->getCustomerId(), $customerIds)) {
                $deletionMessage = "Garage For Guest '" . $garage->getCustomerId() . "' has been deleted.";
                $garage->delete();
                Mage::log($deletionMessage, null, 'garageDeletion.log');
            }
        }
    }

}
