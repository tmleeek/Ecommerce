<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

// We need to loop through every Product Line to see if we can find some duplicate products
// within a product line

foreach (['AVS', 'Lund'] as $storeCategory) {
	// Get category
	$storeCategory = Mage::getModel('catalog/category')->loadByAttribute('name', $storeCategory);
	$childrenCategoryCollection = $storeCategory->getChildrenCategories();

	foreach ($childrenCategoryCollection as $childCategory) {
		// We want to go one level deeper for product lines
		$productLineCollection = $childCategory->getChildrenCategories();
		foreach ($productLineCollection as $productLine) {
			$mmy = [];
			// Now in each product line we need to get the products and see if we have any collisions
			$productCollection = $productLine->getProductCollection()->clear()->addAttributeToSelect('compatible_vehicles');
			foreach ($productCollection as $product) {
				$mmy[$product->getSku()] = explode(',', rtrim($product->getCompatibleVehicles(), ','));
			}
			// Loop through again to check for collision
			foreach ($productCollection as $product) {

				$compatibleVehicles = explode(',', rtrim($product->getCompatibleVehicles(), ','));
				foreach ($mmy as $productSku => $_compatibleVehicles) {
					if ($productSku == $product->getSku())
						continue;

					$intersect = array_intersect($compatibleVehicles, $_compatibleVehicles);
					if (count($intersect) === 0)
						continue;

					// var_dump($compatibleVehicles, $_compatibleVehicles);
					var_dump((int)$product->getSku());
					var_dump($productSku);
					foreach ($intersect as $vehicleId) {
						$vehicle = Mage::getModel('vehicle/ulymm')->load($vehicleId);
						echo $vehicleId . ' ' . $vehicle->getMake() . ' ' . $vehicle->getModel() . ' ' . $vehicle->getYear() . PHP_EOL;
					}
					var_dump($intersect);exit;
				}
			}
			exit;
		}
	}
}