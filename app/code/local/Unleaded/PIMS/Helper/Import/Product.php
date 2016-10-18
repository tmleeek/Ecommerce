<?php

class Unleaded_PIMS_Helper_Import_Product extends Unleaded_PIMS_Helper_Data
{	
	const TAXABLE_GOODS = 2;

	protected $currentSku = false;
	protected $row 		  = [];
	protected $vehicleType;
	protected $adapter;
	protected $categoryImporter;
	protected $vehicles     = [];
	protected $vehicleCache = [];

    public $storeCategories = [];

    public $saveWithImages = false;

	public function __construct()
	{
		$this->adapter          = Mage::helper('unleaded_pims/import_product_adapter');
		$this->categoryImporter = Mage::helper('unleaded_pims/import_category');
	}

	public function setSaveWithImages($saveWithImages)
	{
		$this->saveWithImages = $saveWithImages;
	}

	public function hasSku()
	{
		return $this->currentSku ? true : false;
	}

	public function newProduct($sku, $row, $vehicleType)
	{
        $this->saveCurrentProduct();
        // Reset
		$this->currentSku  = $sku;
		$this->vehicles    = [];
		$this->row         = $row;
		$this->vehicleType = $vehicleType;
	}

	public function isNewSku($sku)
	{
		return $this->currentSku !== $sku;
	}

	public function addVehicle($year, $make, $model, $subModel, $subDetail)
	{
		if (!isset($this->vehicles[$make]))
			$this->vehicles[$make] = [];

		if (!isset($this->vehicles[$make][$model]))
			$this->vehicles[$make][$model] = [];

		if (!isset($this->vehicles[$make][$model][$subModel]))
			$this->vehicles[$make][$model][$subModel] = [];

		if (!isset($this->vehicles[$make][$model][$subModel][$subDetail]))
			$this->vehicles[$make][$model][$subModel][$subDetail] = [];

		if (!in_array($year, $this->vehicles[$make][$model][$subModel][$subDetail]))
			$this->vehicles[$make][$model][$subModel][$subDetail][] = $year;

		// $this->info($year);
		// $this->info($make);
		// $this->info($model);
		// $this->info($subModel);
		// $this->info($subDetail);
	}

	public function saveCurrentProduct()
	{
		if (!$this->hasSku()) {
		    return;
		}

		$this->debug($this->currentSku);

		// See if the product exists
		$isNewProduct = true;
		$product      = Mage::getModel('catalog/product')->loadByAttribute('sku', $this->currentSku);

		if (!$product || !$product->getId())
			$product = Mage::getModel('catalog/product');
		else
			$isNewProduct = false;

		// Next we need to get the attribute set id
		if ($isNewProduct)
			$attributeSetId = Mage::helper('unleaded_pims/import_product_attributesets')->getSetId($this->row, $this->currentSku);
		else
			$attributeSetId = $product->getAttributeSetId();

		// Get base product data for new products, otherwise start blank array
		if ($isNewProduct)
			$_productData = $this->getBaseProductData($attributeSetId);
		else
			$_productData = [];

		// Now get the attribute with name, attributes, and the set itself
		$attributeSetData = $this->adapter->getAttributeSetData($attributeSetId);

		// Map the product data based on the attribute set
		$_productData = $this->adapter->mapAttributeSetData($this->row, $_productData, $attributeSetData);

		// Make sure the options exist for YMM
		$this->createNewYMMOptions();

		$_productData['vehicle_type']        = $this->adapter->getVehicleTypeOptionId($this->vehicleType);
		$_productData['compatible_vehicles'] = $this->getCompatibleVehicles();
		$_productData['category_ids']        = $this->getCategories();


		try {
			if (!$isNewProduct) {
                unset($_productData['url_key']);
            }

			foreach ($_productData as $key => $value) {
                $product->setData($key, $value);
            }

			$product->save();

		} catch (Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }

		// Now we need to get the images
		if ($this->saveWithImages)
			$this->saveProductImages($product);

		// Now we need to check if a configurable product is necessary		
	}

    function setStore($store) 
    {
        $this->storeCategories = [];
        foreach (Mage::app()->getStores() as $_store) {
            if (in_array($_store->getCode(), ['default', $store])) {
                $this->storeCategories[] = $_store->getRootCategoryId();
            }
        }
    }

	protected function getCategories()
	{
		$categories = [];
		// We will need to drop this into multiple categories, and log errors if the 
		// category doesn't exist

		$categoryName = $this->row['Product Category Short Code'];

        foreach ($this->storeCategories as $storeCategoryId) {
			$category = Mage::getModel('catalog/category')->load($storeCategoryId);
			$like     = '1/' . $storeCategoryId . '/%';

            $category = Mage::getModel('catalog/category')
			                ->getCollection()
			                ->addAttributeToSelect('product_category_short_code')
			                ->addAttributeToFilter('product_category_short_code', $categoryName)
			                ->addFieldToFilter('path', ['like' => $like])
			                ->getFirstItem();

            foreach (explode('/', $category->getPath()) as $categoryId) {
                $categories[] = $categoryId;
            }
        }

		return $categories;
	}

	protected function createNewYMMOptions()
	{
		foreach ($this->vehicles as $make => $models) {
			$makeOptionId = $this->adapter->getOptionId('make', ['Make' => $make]);
			foreach ($models as $model => $subModels) {
				$modelOptionId = $this->adapter->getOptionId('model', ['Model' => $model]);
				foreach ($subModels as $subModel => $subDetails) {
					$subModelOptionId = $this->adapter->getOptionId('sub_model', ['SubModel' => $subModel]);
					foreach ($subDetails as $subDetail => $years) {
						$subDetailOptionId = $this->adapter->getOptionId('sub_detail', ['SubDetail' => $subDetail]);
						foreach ($years as $year) {
							$yearOptionId = $this->adapter->getOptionId('year', ['Year' => $year]);
						}
					}
				}
			}
		}
	}


	protected function getCompatibleVehicles()
	{
		$vehicles = [];
		foreach ($this->vehicles as $make => $models) {
			foreach ($models as $model => $subModels) {
				foreach ($subModels as $subModel => $subDetails) {
					foreach ($subDetails as $subDetail => $years) {
						foreach ($years as $year) {
							// Try to find it in the cache
							if ($vehicleId = $this->isInVehicleCache($year, $make, $model, $subModel, $subDetail)) {
								$vehicles[] = $vehicleId;
								continue;
							}

							$vehicleCollection = Mage::getModel('vehicle/ulymm')
												->getCollection()
												->addFieldToFilter('year', $year)
												->addFieldToFilter('make', $make)
												->addFieldToFilter('model', $model)
												->addFieldToFilter('sub_model', $subModel)
												->addFieldToFilter('sub_detail', $subDetail);

							if ($vehicleCollection->getSize() === 0) {
								// We need to add this vehicle
								try {
									$_vehicle = [
										'year'       => $year,
										'make'       => $make,
										'model'      => $model,
										'sub_model'  => $subModel,
										'sub_detail' => $subDetail,
									];
									$vehicle = Mage::getModel('vehicle/ulymm')->setData($_vehicle)->save();
									$this->addVehicleToCache($vehicle);
									$vehicles[] = $vehicle->getId();
								} catch (Exception $e) {
									$this->error($e->getMessage());
								}
							} else {
								foreach ($vehicleCollection as $vehicle) {
									$this->addVehicleToCache($vehicle);
									$vehicles[] = $vehicle->getId();
								}
							}
						}
					}
				}
			}
		}
		return implode(',', $vehicles);
	}

	protected function isInVehicleCache($year, $make, $model, $subModel, $subDetail)
	{
		if (!isset($this->vehicleCache[$make]))
			return false;

		if (!isset($this->vehicleCache[$make][$model]))
			return false;

		if (!isset($this->vehicleCache[$make][$model][$subModel]))
			return false;

		if (!isset($this->vehicleCache[$make][$model][$subModel][$subDetail]))
			return false;

		if (!array_key_exists($year, $this->vehicleCache[$make][$model][$subModel][$subDetail]))
			return false;

		return $this->vehicleCache[$make][$model][$subModel][$subDetail][$year];
	}

	protected function addVehicleToCache($vehicle)
	{
		$make      = $vehicle->getMake();
		$model     = $vehicle->getModel();
		$subModel  = $vehicle->getSubModel();
		$subDetail = $vehicle->getSubDetail();
		$year      = $vehicle->getYear();

		if (!isset($this->vehicleCache[$make]))
			$this->vehicleCache[$make] = [];

		if (!isset($this->vehicleCache[$make][$model]))
			$this->vehicleCache[$make][$model] = [];

		if (!isset($this->vehicleCache[$make][$model][$subModel]))
			$this->vehicleCache[$make][$model][$subModel] = [];

		if (!isset($this->vehicleCache[$make][$model][$subModel][$subDetail]))
			$this->vehicleCache[$make][$model][$subModel][$subDetail] = [];

		if (!array_key_exists($year, $this->vehicleCache[$make][$model][$subModel][$subDetail]))
			$this->vehicleCache[$make][$model][$subModel][$subDetail][$year] = $vehicle->getId();
	}

	protected function getBaseProductData($attributeSetId)
	{
		return [
			'sku'              => $this->currentSku,
			'type_id'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
			'tax_class_id'     => self::TAXABLE_GOODS,
			'website_ids'      => [1],
			'is_massupdate'    => true,
			'attribute_set_id' => $attributeSetId,
			'category_ids'     => [],
			'visibility'       => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
			'media_gallery'    => [
				'images'				  => []
			],
			'stock_data'       => [
				'use_config_manage_stock' => 0, 
				'manage_stock'            => 1, 
				'is_in_stock'             => 1,
				'qty'					  => 0
			]
		];
	}

	protected function saveProductImages(Mage_Catalog_Model_Product $product)
	{
		$images = [
			'p01_off_vehicle'   => 'P01 - Off Vehicle',
			'p03_lifestyle'     => 'P03 - Lifestyle',
			'p04_primary_photo' => 'P04 - Primary Photo',
			'p05_closeup'       => 'P05 - Closeup',
			'p06_mounted'       => 'P06 - Mounted',
			'p07_unmounted'     => 'P07 - Unmounted'
		];

		foreach ($images as $attributeCode => $field) {
			// First check to see if we have data
			if ($this->row[$field] === '') {
			    continue;
            }

			// Try to download the image
			if (!$localPath = Mage::helper('unleaded_pims/ftp')->getPartsImage($this->row[$field]))
				return $this->error('Unable to download image');

			// Grab the media gallery and make sure we don't duplicate
			$product->load('media_gallery');
			$mediaGallery = $product->getMediaGallery();

			foreach ($mediaGallery['images'] as $image) {
				// Check to see if we already have this in the gallery
				if (strstr($image['file'], $this->row[$field])) {
					// Make sure it exists on the disk
					$mageImagePath = Mage::getBaseDir('media') . '/catalog/product' . $image['file'];
					if (!file_exists($mageImagePath)) {
						// We have this in our media gallery but it does not exist on disk, we need to
						// remove it from the DB and save this image to disk and DB
						$this->removeImageReferenceFromMediaGallery($product, $mediaGallery, $image['file']);
						if (!copy($localPath, $mageImagePath))
							$this->error('Problem overwriting catalog image');
					}
					// Either way, we already have this image in our media gallery, so continue to next image
					continue 2;
				}
			}

			// Set the image to it's respective attribute code, and also the default images 
			// if it's the primary photo
			$mediaAttribute = [$attributeCode];
			if ($attributeCode === 'p04_primary_photo')
				$mediaAttribute = array_merge($mediaAttribute, ['image', 'small_image', 'thumbnail']);
			
			// Now that we have local path we can add image to gallery	
			try {
				$product
					->addImageToMediaGallery($localPath, $mediaAttribute, false, false)
					->save();
			} catch (Exception $e) {
				$this->error($e->getMessage());
				$this->error('Unable to save image');
			}
		}
	}

	protected function removeImageReferenceFromMediaGallery($product, $mediaGallery, $filePath)
	{
		// Iterate through gallery to find position of filePath in question
		foreach ($mediaGallery['images'] as $index => $image)
			if ($image['file'] === $filePath) {
				unset($mediaGallery['images'][$index]);
				break;
			}

		// Now save the gallery back to the product
		try {
			$product->setData('media_gallery', $mediaGallery);
			$product->getResource()->save($product);
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}	
	}
}