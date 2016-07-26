<?php

class Unleaded_Vehicle_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $layout = Mage::getSingleton('core/layout');
        $html = $layout
                ->createBlock('core/template')
                ->setTemplate('ulvehicle/vehicle-selector.phtml')
                ->toHtml();
        echo $html;
    }

    public function removeVehicleAction() {
        $vehicleId = Mage::app()->getRequest()->getParam('vehicleId');
        $customerId = Mage::app()->getRequest()->getParam('customerId');

        $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
        $garageModel->addFieldToFilter('customer_id', $customerId);

        $garageData = $garageModel->getFirstItem();
        $garage = json_decode($garageData->getVehicles());

        if (($key = array_search($vehicleId, $garage)) !== false) {
            unset($garage[$key]);
            if ($garageData->getSelectedVehicle() && $garageData->getSelectedVehicle() == $vehicleId) {
                $garageData->setSelectedVehicle(0);
            }
        }
        $garageData->setVehicles(json_encode(array_values($garage)));
        $garageData->save();

        $this->loadLayout();
        $layout = Mage::getSingleton('core/layout');
        $html = $layout
                ->createBlock('vehicle/vehicle')
                ->setTemplate('ulvehicle/garage.phtml')
                ->toHtml();
        echo $html;
    }

    public function clearAllAction() {
        $customerId = Mage::app()->getRequest()->getParam('customerId');

        $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
        $garageModel->addFieldToFilter('customer_id', $customerId);

        $garageId = $garageModel->getFirstItem()->getId();
        Mage::getModel('vehicle/ulgarage')->setId($garageId)->delete();

        // Clear cookie
        Mage::getSingleton('core/cookie')->delete('currentVehicle');

        echo Mage::getBaseUrl();
    }

    public function changeSelectionAction() {
        $vehicleId = Mage::app()->getRequest()->getParam('vehicleId');
        $customerId = Mage::app()->getRequest()->getParam('customerId');

        $garageModel = Mage::getModel('vehicle/ulgarage')->getCollection();
        $garageModel->addFieldToFilter('customer_id', $customerId);

        $garageData = $garageModel->getFirstItem();
        $garageData->setSelectedVehicle($vehicleId);
        $garageData->save();

        $this->loadLayout();
        $layout = Mage::getSingleton('core/layout');
        $html = $layout
                ->createBlock('vehicle/vehicle')
                ->setTemplate('ulvehicle/garage.phtml')
                ->toHtml();
        echo $html;
    }

    public function addVehicleAndRedirectAction() {
        $request = $this->getRequest();

        $year = $request->getParam('year');
        $make = $request->getParam('make');
        $model = $request->getParam('model');
        $targetCategoryId = $request->getParam('targetCategoryId');

        $_vehicle = Mage::getModel("vehicle/ulymm")
                ->getCollection()
                ->addFieldToFilter('year', $year)
                ->addFieldToFilter('make', $make)
                ->addFieldToFilter('model', $model)
                ->getFirstItem();

        $vehicleId = $_vehicle->getId();

        $customerId = Mage::app()->getRequest()->getParam('customerId');

        $garageModel = Mage::getModel('vehicle/ulgarage')
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId);

        $vehicleBlock = $this->getLayout()->getBlockSingleton('vehicle/vehicle');

        // Set cookie for vehicle
        $cookie = Mage::getSingleton('core/cookie');
        $cookie->set(
                'currentVehicle', Mage::helper('unleaded_ymm')->getVehicleSegment($year, $make, $model), (60 * 60 * 24 * 30), '/'
        );

        // If there is a targetCategoryId then we need to direct them to the product
        // that exists in this category and also fits this vehicle
        if ($targetCategoryId && $targetCategoryId !== 'undefined')
            $redirectUrl = Mage::helper('unleaded_ymm')->getProductUrl($year, $make, $model, $targetCategoryId);
        else
            $redirectUrl = Mage::helper('unleaded_ymm')->getVehicleUrl($year, $make, $model);


        if ($garageModel->count() == 1) {
            $garageData = $garageModel->getFirstItem();
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $garage = json_decode($garageData->getVehicles());
                if (!in_array($vehicleId, $garage)) {
                    $garage[] = $vehicleId;
                    $garageData->setVehicles(json_encode(array_values($garage)));
                    $garageData->setSelectedVehicle($vehicleId);
                    $garageData->save();
                    echo $redirectUrl;
                } else {
                    $garageData->setSelectedVehicle($vehicleId);
                    $garageData->save();
                    echo $redirectUrl;
                }
            } else {
                $garage = [$vehicleId];
                $garageData->setVehicles(json_encode(array_values($garage)));
                $garageData->setSelectedVehicle($vehicleId);
                $garageData->save();
                echo $redirectUrl;
            }
        } else {
            $newGarage = Mage::getModel('vehicle/ulgarage');
            $newGarage->setCustomerId($customerId);
            $newGarage->setVehicles(json_encode([$vehicleId]));
            $newGarage->setSelectedVehicle($vehicleId);
            $newGarage->save();
            echo $redirectUrl;
        }
    }

    public function getMakeByYearAction() {
        $year = Mage::app()->getRequest()->getParam('year');
        $ymmCollection = Mage::getModel("vehicle/ulymm")->getCollection();
        $ymmCollection->addFieldToFilter('year', $year);

        $yearMake = [];
        foreach ($ymmCollection as $_vehicle) {
            if (!in_array($_vehicle->getMake(), $yearMake)) {
                $yearMake[] = $_vehicle->getMake();
            }
        }
        sort($yearMake);
        echo json_encode($yearMake);
    }

    public function getModelByMakeAndYearAction() {
        $year = Mage::app()->getRequest()->getParam('year');
        $make = Mage::app()->getRequest()->getParam('make');

        $ymmCollection = Mage::getModel("vehicle/ulymm")->getCollection();
        $ymmCollection->addFieldToFilter('year', $year);
        $ymmCollection->addFieldToFilter('make', $make);
        $yearMakeModel = [];

        foreach ($ymmCollection as $_vehicle) {
            if (!in_array($_vehicle->getModel(), $yearMakeModel)) {
                $yearMakeModel[] = $_vehicle->getModel();
            }
        }
        sort($yearMakeModel);
        echo json_encode($yearMakeModel);
    }

    public function getCompatibleVehiclesAction() {
        $request = $this->getRequest();
        $target = $request->getParam('target');

        // We need to get the category slug, first make sure we remove all query params
        $target = preg_replace('/\?.*/', '', $target);
        // Also remove our url
        $target = str_replace(Mage::getBaseUrl(), '', $target);
        // Category we want will be the last segment
        $segments = explode('/', $target);
        // Now get the slug
        $slug = $segments[count($segments) - 1];


        $category = Mage::getModel('catalog/category')
                ->loadByAttribute('url_key', $slug);

        // Load up the category and get all products
        $productCollection = Mage::getModel('catalog/category')
                ->loadByAttribute('url_key', $slug)
                ->getProductCollection()
                ->addAttributeToSelect('compatible_vehicles');

        $compatibleVehicleIds = '';
        foreach ($productCollection as $product)
            if ($product->getCompatibleVehicles())
                $compatibleVehicleIds .= $product->getCompatibleVehicles() . ',';

        $compatibleVehicleIds = array_unique(explode(',', $compatibleVehicleIds));

        $this->loadLayout();
        $layout = Mage::getSingleton('core/layout');
        $html = $layout
                ->createBlock('core/template')
                ->setTemplate('ulvehicle/vehicle-selector-prefilled.phtml')
                ->setData('compatible_vehicle_ids', $compatibleVehicleIds)
                ->setData('target_category_id', $category->getId())
                ->toHtml();

        echo $html;
    }

}
