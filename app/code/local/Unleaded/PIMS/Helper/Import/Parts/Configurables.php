<?php

class Unleaded_PIMS_Helper_Import_Parts_Configurables extends Unleaded_PIMS_Helper_Data
{
	protected $productTree        = [];
	protected $lundIntlStoreCategoryId;
	protected $parentCategoryCache = [];

	protected $concatAttributes = [
		'compatible_vehicles'     => 'compatibleVehicles',
		// These are the product configuration attributes
		'bed_length'              => 'bedLength',
		'bed_type'                => 'bedType',
		'flare_height'            => 'flareHeight',
		'flare_tire_coverage'     => 'flareTireCoverage', 
		'box_style'               => 'boxStyle',
		'box_opening'             => 'boxOpening',
		'color'                   => 'color',
		'style'                   => 'style',
		'finish'                  => 'finish',
		'material'                => 'material',
		'material_thickness'      => 'materialThickness',
		'sold_as'                 => 'soldAs',
		'tube_shape'              => 'tubeShape',
		'tube_size'               => 'tubeSize',
		'liquid_storage_capacity' => 'liquidStorageCapacity',
		// These are the additional attributes
		'pop_code'                => 'popCode',
		'brand_short_code'        => 'brandShortCode',
		'i_sheet'                 => 'iSheet',
		'model_type'              => 'modelType',
		'vehicle_type'            => 'vehicleType',
		'height'                  => 'height',
		'width'                   => 'width',
		'length'                  => 'length',
		'country_of_manufacture'  => 'countryOfManufacture'
	];
	protected $compatibleVehicles    = [];

	protected $bedLength             = [];
	protected $bedType               = [];
	protected $flareHeight           = [];
	protected $flareTireCoverage     = [];
	protected $boxStyle              = [];
	protected $boxOpening            = [];
	protected $color                 = [];
	protected $style                 = [];
	protected $finish                = [];
	protected $material              = [];
	protected $materialThickness     = [];
	protected $soldAs                = [];
	protected $tubeShape             = [];
	protected $tubeSize              = [];
	protected $liquidStorageCapacity = [];
	
	protected $popCode               = [];
	protected $brandShortCode        = [];
	protected $iSheet                = [];
	protected $modelType             = [];
	protected $vehicleType           = [];
	protected $height                = [];
	protected $width                 = [];
	protected $length                = [];
	protected $countryOfManufacture  = [];

	public function checkConfigurables()
	{
		$productLineCollection = Mage::getModel('unleaded_productline/productline')->getCollection();

		// We will save the entire json tree into a product line simple product
		foreach ($productLineCollection as $productLine) {
			$this->resetProductTree();

			$attributesToSelect = array_merge([
				'product_line', 
				'name',
			], array_keys($this->concatAttributes));

			// $this->info(print_r($attributesToSelect, true));

			$productCollection = Mage::getModel('catalog/product')
									->getCollection()
									->addAttributeToSelect($attributesToSelect)
									->addAttributeToFilter('product_line', $productLine->getId());

			if ($productCollection->getSize() === 0) 
				continue;

			// We need to add this SKU to the product line tree
			foreach ($productCollection as $product) {
				if (substr($product->getSku(), 0, 3) === 'PL-')
					continue;
				
				// $this->info('Product Status ' . $product->getSku() . ' ' . $product->getStatus());
				// Make sure the product is enabled				
				if ($product->getStatus() === Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
					continue;

				$this->addProductToTree($product);
			}

			$productTree = json_encode($this->productTree);

			// Now we need to get this product line's simple product so we can update it
			// with compatible vehicles and also the product tree
			// Pass in a copy of the last product so we can copy images over if we are creating a new product line product
			$productLineProduct = $this->getProductLineProduct($productLine, $product);
			
			$this->info('Product Line - ' . $productLine->getName() . ' - Tree size - ' . strlen($productTree) / 1000 . ' kb');

			// Clean up concat attributes
			$this->cleanUpConcatAttributes();

			try {
				// Save concatenated data
				foreach ($this->concatAttributes as $attributeCode => $camelCase) {
					// Make sure we only save unique values
					$values = trim(implode(',', $this->$camelCase), ',');
					// $this->info('-- Saving ' . $attributeCode . ' - value - ' . $values);
					// $this->info('---- String Length: ' . strlen($values));
					$productLineProduct->setData($attributeCode, $values);
				}
				$productLineProduct->save();

				$productLine
					->setProductTree($productTree)
					->save();

			} catch (Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}

	private function cleanUpConcatAttributes()
	{
		// Remove consecutive commas
		foreach ($this->concatAttributes as $attributeCode => $camelCase) {
			$this->$camelCase = array_unique($this->$camelCase);
		}
	}

	protected function getProductLineProduct($productLine, $product)
	{
		// Sku is 'PL' plus the product line's ID
		$productLineSku     = 'PL-' . $productLine->getId();
		$productLineProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $productLineSku);

		if ($productLineProduct)
			return $productLineProduct;

		// Product doesn't exist, we need to create it
		$productLineProduct = Mage::getModel('catalog/product');

		$description      = $productLine->getDescription() ? $productLine->getDescription() : '<!--empty-->';
		$shortDescription = $productLine->getShortDescription() ? $productLine->getShortDescription() : '<!--empty-->';
		try {
			$productLineProduct
			    ->setWebsiteIds([1])
			    ->setAttributeSetId(4)
			    ->setTypeId('simple')

			    ->setSku($productLineSku)
			    ->setName($productLine->getName())
			    ->setWeight(0)
			    ->setStatus(1)
			    ->setTaxClassId(0)
			    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)

			    ->setPrice(0.00)

			    ->setDescription($description)
			    ->setShortDescription($shortDescription)

			    ->setMediaGallery(['images' => [], 'values' => []])

			    ->setCategoryIds($this->getCategories($productLine))

			    ->setProductLine($productLine->getId());

			$productLineProduct->save();

			// Now add in images
			$product = Mage::getModel('catalog/product')->load($product->getId());
			$images  = [];
			$baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
		    foreach ($product->getMediaAttributes() as $imageAttribute) {
				$imageAttributeCode = $imageAttribute->getAttributeCode();
				$file               = $baseDir . $product->getData($imageAttributeCode);
				// $this->debug($imageAttributeCode);
				// $this->debug($file);
		        if (file_exists($file)) {
		            if (!isset($images[$file])) {
		                $images[$file] = [];
		            }
		            $images[$file][] = $imageAttributeCode;
		        }
		    }
		    // $this->debug(print_r($images, true));
		    foreach ($images as $file => $imageAttributeList) {
		    	// Make sure we actually have an image here
		    	if (!preg_match('/^.*product.*\.[a-zA-Z]{3,4}$/', $file))
		    		continue;
		        try {
		            $productLineProduct
		            	->addImageToMediaGallery($file, $imageAttributeList, false, false)
		            	->save();
		        } catch (Exception $e) {
		            $this->error($e->getMessage());
		        }
		    }

		} catch (Exception $e) {
			$this->error($e->getMessage());
			return false;
		}

		return $productLineProduct;
	}

	protected function getLundIntlStoreCategoryId()
	{
		if (!$this->lundIntlStoreCategoryId) {
			foreach (Mage::app()->getStores() as $_store) {
	            if ($_store->getCode() !== 'default') {
	            	continue;
	            }
	            $this->lundIntlStoreCategoryId = $_store->getRootCategoryId();
	        }
		}
		return $this->lundIntlStoreCategoryId;
	}

	protected function getParentCategoryName($parentCategoryId)
	{
		if (!isset($this->parentCategoryCache[$parentCategoryId])) {
			$parentCategory = Mage::getModel('catalog/category')->load($parentCategoryId);
			$this->parentCategoryCache[$parentCategoryId] = $parentCategory->getName();
		}
		return $this->parentCategoryCache[$parentCategoryId];
	}

	protected function getCategories($productLine)
	{
		$category   = Mage::getModel('catalog/category')->load($productLine->getParentCategoryId());
		$categories = explode('/', $category->getPath());
		// We also need to make sure we add to the 'Lund Intl' category tree
		$like = '1/' . $this->getLundIntlStoreCategoryId() . '/%';
		$adjacentCategory = Mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect('name')
							->addAttributeToFilter('name', $this->getParentCategoryName($productLine->getParentCategoryId()))
							->addFieldToFilter('path', ['like' => $like])
							->getFirstItem();
		// If there is an adjacent category, add it's path
		if ($adjacentCategory) {
			$categories = array_unique(array_merge($categories, explode('/', $adjacentCategory->getPath())));
		}

		return $categories;
	}

	protected function resetProductTree()
	{
		$this->productTree           = [];

		$this->compatibleVehicles    = [];

		$this->bedLength             = [];
		$this->bedType               = [];
		$this->flareHeight           = [];
		$this->flareTireCoverage     = [];
		$this->boxStyle              = [];
		$this->boxOpening            = [];
		$this->color                 = [];
		$this->style                 = [];
		$this->finish                = [];
		$this->material              = [];
		$this->materialThickness     = [];
		$this->soldAs                = [];
		$this->tubeShape             = [];
		$this->tubeSize              = [];
		$this->liquidStorageCapacity = [];
		
		$this->popCode               = [];
		$this->brandShortCode        = [];
		$this->iSheet                = [];
		$this->modelType             = [];
		$this->vehicleType           = [];
		$this->height                = [];
		$this->width                 = [];
		$this->length                = [];
		$this->countryOfManufacture  = [];
	}

	protected function addProductToTree($product)
	{
		foreach ($this->concatAttributes as $attributeCode => $camelCase) {
			// Set local variable value
			// !!!! This is where the $bedLength, $bedType, $flareHeight, etc.... !!!!
			// !!!! variables below are set !!!!
			$$camelCase = $product->getData($attributeCode);

			// $this->info('----- ' . $attributeCode . ' ' . $$camelCase);

			// Concat global variable with value
			array_push($this->$camelCase, $$camelCase);
		}

		// First we add the compatible vehicles
		$YMMs = $this->getYMMsFromString($product->getCompatibleVehicles());
		foreach ($YMMs as $vehicle) {
			$year      = $vehicle->getYear();
			$make      = $vehicle->getMake();
			$model     = $vehicle->getModel();
			$subModel  = $vehicle->getSubModel();
			$subDetail = $vehicle->getSubDetail();

			// Make sure this vehicle is in the tree (vehicle specific attributes)
			if (!isset($this->productTree[$year]))
				$this->productTree[$year] = [];
			if (!isset($this->productTree[$year][$make]))
				$this->productTree[$year][$make] = [];
			if (!isset($this->productTree[$year][$make][$model]))
				$this->productTree[$year][$make][$model] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel]))
				$this->productTree[$year][$make][$model][$subModel] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail] = [];

			// These are the product specific attributes
			// !!!! These are the variables we mentioned above !!!!
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape][$tubeSize]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape][$tubeSize] = [];
			if (!isset($this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape][$tubeSize][$liquidStorageCapacity]))
				$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape][$tubeSize][$liquidStorageCapacity] = [];

			$this->productTree[$year][$make][$model][$subModel][$subDetail][$bedLength][$bedType][$flareHeight][$flareTireCoverage][$boxStyle][$boxOpening][$color][$finish][$style][$material][$materialThickness][$soldAs][$tubeShape][$tubeSize][$liquidStorageCapacity][] = $product->getSku();
		}
	}

	public function getYMMs($vehicleIds)
	{
		return array_map(function($vehicleId) {
			return Mage::getModel('vehicle/ulymm')->load($vehicleId);
		}, $vehicleIds);
	}

	public function getYMMsFromString($vehicles)
	{
		return $this->getYMMs(explode(',', rtrim($vehicles, ',')));
	}
}