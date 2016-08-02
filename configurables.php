<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

// These are the attribute codes that can be differentiating for a configurable product
$_differentiatingAttributes = [
	'sub_detail', 'sub_model', 'bed_length', 'bed_type', 'color'
];

// Map the ids to the codes
$differentiatingAttributes = array_combine($_differentiatingAttributes, array_map(function($attributeCode) {
	return Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode)->getId();
}, $_differentiatingAttributes));
// var_dump($differentiatingAttributes);

$dataSkip = [
	'entity_id',
	'entity_type_id',
	'type_id',
	'sku',
	'has_options',
	'required_options',
	'created_at',
	'updated_at',
	'options_container',
	'is_returnable',
	'url_key',
	'color',
	'bed_length',
	'bed_type',
	'stock_item',
	'stock_data',
	'is_in_stock',
	'is_salable'
];

$attributeValueCache = [];

// We need to loop through every Product Line to see if we can find some duplicate products
// within a product line
foreach (['AVS', 'Lund'] as $storeCategory) {
	// Get category
	$storeCategory = Mage::getModel('catalog/category')->loadByAttribute('name', $storeCategory);
	$childrenCategoryCollection = $storeCategory->getChildrenCategories();

	// Loop through the main categories, example: Hood Protection, Running Boards
	foreach ($childrenCategoryCollection as $childCategory) {
		// We want to go one level deeper for product lines
		$productLineCollection = $childCategory->getChildrenCategories();

		// Loop through product collections, example: CARFLECTOR, BUGFLECTOR
		foreach ($productLineCollection as $productLine) {
			// Store each product's compatible vehicles here
			$products = [];

			// Now in each product line we need to get the products and see if we have any collisions
			$productCollection = $productLine
									->getProductCollection()
									->clear()
									->addAttributeToSelect('compatible_vehicles');

			// Loop through product collection for this product line
			foreach ($productCollection as $product) {
				// Store compatible vehicles for all products in this line
				$products[$product->getSku()] = explode(',', rtrim($product->getCompatibleVehicles(), ','));
			}

			// Loop through again to check for collision
			foreach ($productCollection as $product) {
				// Get compatible vehicles for this product
				$compatibleVehicles = explode(',', rtrim($product->getCompatibleVehicles(), ','));

				// Loop through products cache and search for collisions
				foreach ($products as $productSku => $_compatibleVehicles) {
					// Skip same product
					if ($productSku == $product->getSku())
						continue;

					$intersect = array_intersect($compatibleVehicles, $_compatibleVehicles);
					sort($intersect);
					// No collision
					if (count($intersect) === 0 || (count($intersect) === 1 && $intersect[0] === ''))
						continue;

					// This is a collision, this means that we need to create a configurable product
					// BUT ONLY IF THERE IS DIFFERENTIATING ATTRIBUTE DATA

					// Load the colliding product
					$collidingProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
					// Load this product so we have all attribute data
					$product = Mage::getModel('catalog/product')->load($product->getId());

					$_diff = [];
					foreach ($differentiatingAttributes as $attributeCode => $attributeId) {
						if ($product->getData($attributeCode) !== $collidingProduct->getData($attributeCode)) {
							// We have some differentiating data
							$_diff[$attributeCode] = [
								$product->getId()          => $product->getData($attributeCode),
								$collidingProduct->getId() => $collidingProduct->getData($attributeCode)
							];
						}
					}

					// If there isn't any differentiating data, we need to make note
					if (count($_diff) === 0) {
						$MMYs = implode(PHP_EOL, array_map(function($vehicleId) {
							$vehicle = Mage::getModel('vehicle/ulymm')->load($vehicleId);
							return $vehicleId . ' - ' 
									. $vehicle->getMake() . ' ' 
									. $vehicle->getModel() . ' ' 
									. $vehicle->getYear();
						}, $intersect));

						// echo 'Part # ' . $product->getSku() . ' and # ' . $collidingProduct->getSku() 
						// 	. ' are unique for these MMYs but there is no differentiating data to '
						// 	. 'create a configurable product with:' . PHP_EOL . $MMYs . PHP_EOL;
					} else {
						// var_dump($_diff);exit;
						// var_dump(count($products));exit;
						// var_dump($usedAttributeIds);exit;

						// We need to create a configurable product with the differential data
						$configurableProduct = Mage::getModel('catalog/product')
												->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

						$configurableProduct->setData('is_massupdate', true);

						// $media = [
						// 	'images'				  => []
						// ];
						// $configurableProduct->setData('media_gallery', $media);

						$stock = [
							'use_config_manage_stock' => 0,
							'manage_stock'            => 1,
							'is_in_stock'             => 1,
						];
						$configurableProduct->setData('stock_data', $stock);

						try {
							$configurableProduct->setAttributeSetId($product->getAttributeSetId());
							$configurableProduct->save();
							// Reload the product otherwise we get integrity constraint on stock item
							$configurableProduct = Mage::getModel('catalog/product')->load($configurableProduct->getId());
						} catch (Exception $e) {
							echo __LINE__ . ' ' . $e->getMessage() . PHP_EOL;
						}
						
						// Give it a sku the same as the entity_id since they will be buying the child product anyways
						$configurableProduct->setSku($configurableProduct->getId());

						$configurableProduct->setCategoryIds($product->getCategoryIds());

						foreach ($product->getData() as $key => $data) {
							if (in_array($key, $dataSkip))
								continue;
							$configurableProduct->setData($key, $data);
						}

						try {
							$configurableProduct->save();
						} catch (Exception $e) {
							echo __LINE__ . ' ' . $e->getMessage() . PHP_EOL;
						}

						// Set up child product relationships
						$productInstance  = $configurableProduct->getTypeInstance();
						$usedAttributeIds = array_values(array_intersect_key($differentiatingAttributes, $_diff));
						$productInstance->setUsedProductAttributeIds($usedAttributeIds);

						// Set configurable attributes
						$configurableAttributesArray = $productInstance->getConfigurableAttributesAsArray();
						$configurableProduct->setCanSaveConfigurableAttributes(true);
						$configurableProduct->setConfigurableAttributesData($configurableAttributesArray);

						// Set the configurable product data
						$configurableProductData = [];
						// Loop throught he differentiating attribute codes
						foreach ($_diff as $attributeCode => $data) {
							// Get the id for the attribute from our map at the beginning
							$attributeId = $differentiatingAttributes[$attributeCode];
							// Loop through the products for this attribute code
							foreach ($data as $productId => $attributeOptionId) {
								// See if this attribute code is in the cache
								if (!isset($attributeValueCache[$attributeCode]))
									$attributeValueCache[$attributeCode] = [];

								// See if this option value is in the cache
								if (!isset($attributeValueCache[$attributeCode][$attributeOptionId])) {
									$value = Mage::getModel('catalog/product')
												->setStoreId(0)
												->setData($attributeCode, $attributeOptionId)
												->getAttributeText($attributeCode);
									$attributeValueCache[$attributeCode][$attributeOptionId] = $value;
								}

								// Make sure we already have this product's configuration options started
								if (!isset($configurableProductData[$productId]))
									$configurableProductData[$productId] = [];
								// Add this product option configuration
								$configurableProductData[$productId][] = [
									'label'         => $attributeValueCache[$attributeCode][$attributeOptionId],
									'attribute_id'  => $attributeId,
									'value_index'   => $attributeOptionId,
									'is_percent'    => '0',
									'pricing_value' => '0'
								];

								// Also make the child products not visible individually while we're in here
								$action = Mage::getSingleton('catalog/resource_product_action')
								            ->updateAttributes([$productId], [
								                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
								            ], 0);
							}
						}

						$configurableProduct->setConfigurableProductsData($configurableProductData);
						
						try {
							$configurableProduct->save();
						} catch (Exception $e) {
							echo __LINE__ . ' ' . $e->getMessage() . PHP_EOL;
						}

						echo $configurableProduct->getId() . PHP_EOL;
					}
				}
			}
		}
	}
}