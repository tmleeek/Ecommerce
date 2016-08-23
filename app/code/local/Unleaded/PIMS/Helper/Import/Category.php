<?php

class Unleaded_PIMS_Helper_Import_Category extends Unleaded_PIMS_Helper_Data
{
	const ADMIN_STORE_ID      = 0;

	protected $adapter;
	protected $productLineImporter;
	protected $parentPath;
	protected $storeId;
	protected $storeCategoryPath;
    protected $defaultStore;
    protected $defaultCategoryPath;

	protected $fields = [
		'name',
		'description',
		'short_description',
		'is_active',
		'is_anchor',
		'page_title',
		'meta_description',
		'small_image',
		'include_in_menu',
		'display_mode',
		'url_key',
		'product_category_short_code'
	];

	const AVS_SHORTCODE  = 'avs';
	const AVS_CATEGORY   = 'AVS';
	const LUND_SHORTCODE = 'lund';
	const LUND_CATEGORY  = 'Lund';

	protected $parentCategories = [
		'store'   => [],
		'default' => []
	];

	public function __construct()
	{
		$this->adapter             = Mage::helper('unleaded_pims/import_category_adapter');
		$this->productLineImporter = Mage::helper('unleaded_pims/import_productline');
	}

	public function saveCategoryBrands()
	{
		// There will be some circumstances where the a category has both brands, 
		// for example, 'Hood Protection', we need to go through all top level categories 
		// after the import to make sure we have assigned the correct brands to the default store's
		// categories. This attribute will not be used within an individual store view, for example
		// AVS or Lund, but in the Lund International store view we need to know what categories exist
		// in what brand (store) categories

		$this->defaultStore        = Mage::getModel('core/store')->load('default', 'code');
		$this->defaultCategoryPath = '1/' . $this->defaultStore->getRootCategoryId();

		$storeRoots = [];
		foreach (Mage::app()->getStores() as $store) {
        	if ($store->getCode() === 'default')
        		continue;
        	$storeRoots[$store->getRootCategoryId()] = $store->getCode();
        }

		// First grab all second level categories to the default store's root category
		$categoriesToCheck = Mage::getModel('catalog/category')
								->getCollection()
								->addAttributeToSelect('name')
								->addFieldToFilter('path', ['like' => $this->defaultCategoryPath . '/%'])
								->addFieldToFilter('level', 2);

		// Loop through and compile the brands
		foreach ($categoriesToCheck as $category) {
			// Now see what store root categories this category also exists in
			$brandCategories = Mage::getModel('catalog/category')
								->getCollection()
								->addAttributeToSelect('name')
								->addFieldToFilter('name', $category->getName())
								->addFieldToFilter('path', ['nlike' => $this->defaultCategoryPath . '/%'])
								->addFieldToFilter('level', 2);
			// Compile the store codes and save them to the category we are checking
			$_categoryBrands = '';
			foreach ($brandCategories as $brandCategory) {
				// Check if we have this root category's store code and add it if we do
				$rootCategoryId = $brandCategory->getParentCategory()->getId();
				if (isset($storeRoots[$rootCategoryId]))
					$_categoryBrands .= $storeRoots[$rootCategoryId] . ',';
			}
			$_categoryBrands = rtrim($_categoryBrands, ',');

			try {
				// Now save it to the category
				$category->setData('category_brands', $_categoryBrands)->save();
				$this->info($category->getName() . ' saved with brands ' . $_categoryBrands);
			} catch (Exception $e) {
				$this->error($e->getMessage());
			}
		}
	}

	public function setStore($storeCode)
	{
		// Try to load the store via code
		if (!$this->store = Mage::getModel('core/store')->load($storeCode, 'code'))
			return false;

		// Set the store category path
		$this->storeCategoryPath = '1/' . $this->store->getRootCategoryId();
		// Set the current store
		Mage::app()->setCurrentStore(self::ADMIN_STORE_ID);

		// Also set up the default store "Lund International"
		$this->defaultStore        = Mage::getModel('core/store')->load('default', 'code');
		$this->defaultCategoryPath = '1/' . $this->defaultStore->getRootCategoryId();

		// We need to save the category to this store and also the default store
		$this->saveToStores = [
			'store' => [
				'id'   => $this->store->getId(),
				'path' => $this->storeCategoryPath,
				'name' => $this->store->getName(),
				'code' => $this->store->getCode()
			],
			'default' => [
				'id'   => $this->defaultStore->getId(),
				'path' => $this->defaultCategoryPath,
				'name' => $this->defaultStore->getName(),
				'code' => $this->defaultStore->getCode()
			]
		];

		return true;
	}

	public function import($row)
	{
		$_category = [];

		// Each row is a "Product Line"
		// First check to see if this Product Line's parent category exists
		$productLineName    = $this->productLineImporter->adapter->getMappedValue('name', $row);
		$parentCategoryName = $this->adapter->getMappedValue('name', $row);
		
		// We need to save all categories to their 'store' (Lund, AVS, etc...) 
		// and also the default store (Lund International)
		foreach ($this->saveToStores as $identifier => $storeData) {
			// First check cache
			if (isset($this->parentCategories[$identifier][$productLineName])) {
                $parentCategory = $this->parentCategories[$identifier][$productLineName];
            } else {
				// If it's not in the cache, we need to search for it
				$parentCategory = Mage::getModel('catalog/category')
									->getCollection()
									->addFieldToFilter('path', ['like' => $storeData['path'] . '/%'])
									->addFieldToFilter('name', $parentCategoryName)
									->getFirstItem();
			}

			// Check that we have found a parent category
			if (!$parentCategory || !$parentCategory->getId()) {
				// This is a parent category that does not exist yet, so we need
				// to create it with the path set to the store's (brand's) category
				if (!$parentCategory = $this->newParentCategory($row, $storeData)) {
					$this->error('Unable to create parent category');
					return;
				}
			} else {
				// Update the parent category
				if (!$parentCategory = $this->updateParentCategory($row, $parentCategory)) {
					$this->error('Unable to update the parent category');
					return;
				}
			}

			// Since we may have just loaded or created a category, add it to the cache
			if (!isset($this->parentCategories[$identifier][$productLineName]))
				$this->parentCategories[$identifier][$productLineName] = $parentCategory;

			// Now we check out this product line and make updates but only if this 
			// is the actual store parent category
			if ($identifier === 'default')
				continue;

			$this->productLineImporter->import($parentCategory, $row);
		}
	}

	public function newParentCategory($row, $storeData)
	{
		$categoryData = [];

		// Map category data
		foreach ($this->fields as $field)
			$categoryData[$field] = $this->adapter->getMappedValue($field, $row);

		// Get new category model
		$category  = Mage::getModel('catalog/category');

		// Merge data we previously collected with this store's info
		$_category = array_merge($categoryData, [
			'store_id' => $storeData['id'],
			'path'	   => $storeData['path']
		]);

		try {
	    	foreach ($_category as $key => $value)
	    		$category->setData($key, $value);

	    	$category->save();

	    	// Save image to global also
	    	// Mage::app()->setCurrentStore(0);
	    	// $category = Mage::getModel('catalog/category')->loadByAttribute('name', $name);
	    	// $category->setData('image', $_category['image'])->save();

	    	$this->debug('Category imported successfully - ' . $category->getName() . ' for store ' . $storeData['name']);

	    	return $category;
	    } catch (Exception $e) {
	    	$this->error($e->getMessage());
	    	return false;
	    }
	}

	public function updateParentCategory($row, $parentCategory)
	{
		$categoryData = [];

		// Map category data
		foreach ($this->fields as $field)
			$categoryData[$field] = $this->adapter->getMappedValue($field, $row);

		try {
	    	foreach ($categoryData as $key => $value)
	    		$parentCategory->setData($key, $value);

	    	$parentCategory->save();

	    	// Save image to global also
	    	// Mage::app()->setCurrentStore(0);
	    	// $category = Mage::getModel('catalog/category')->loadByAttribute('name', $name);
	    	// $category->setData('image', $_category['image'])->save();

	    	$this->debug('Category updated successfully - ' . $parentCategory->getName());

	    	return $parentCategory;
	    } catch (Exception $e) {
	    	$this->error($e->getMessage());
	    	return false;
	    }
	}
}