<?php

class Unleaded_PIMS_Helper_Import_Category extends Unleaded_PIMS_Helper_Data
{
	const ADMIN_STORE_ID      = 0;
	const DEFAULT_CATEGORY_ID = 2;

	protected $adapter;
	protected $parentPath;
	protected $storeId;
	protected $storeCategoryPath;

	protected $parentsUpdated = [];

	protected $fields = [
		'name',
		'description',
		'is_active',
		'is_anchor',
		'page_title',
		'meta_description',
		'image',
		'include_in_menu',
		'display_mode',
		'short_description',
		'product_category_display_name'
	];

	const AVS_SHORTCODE  = 'avs';
	const AVS_CATEGORY   = 'AVS';
	const LUND_SHORTCODE = 'lund';
	const LUND_CATEGORY  = 'Lund';

	public function __construct()
	{
		$this->adapter  = Mage::helper('unleaded_pims/import_category_adapter');
		$this->rootPath = Mage::getModel('catalog/category')->load(2)->getPath();
	}

	public function setStore($storeCode)
	{
		// $this->storeId  = Mage::getModel('core/store')->loadConfig($storeCode)->getId();

		switch ($storeCode) {
			case self::AVS_SHORTCODE;
				$name = self::AVS_CATEGORY;
				break;
			case self::LUND_SHORTCODE;
				$name = self::LUND_CATEGORY;
				break;
			default;
				$name = false;
		}

		$this->storeCategoryPath = Mage::getModel('catalog/category')
									->getCollection()
									->addAttributeToFilter('name', $name)
									->getFirstItem()
									->getPath();
	}

	protected $parentCategories = [];
	public function import($row)
	{
		$_category = [];
		Mage::app()->setCurrentStore(self::ADMIN_STORE_ID);

		// Each row is a "Product Line"
		// First check to see if this Product Line's parent category exists
		$productLineName    = $this->adapter->getMappedValue('name', $row, true);
		$parentCategoryName = $this->adapter->getMappedValue('name', $row, false);
		
		// First check cache
		if (isset($this->parentCategories[$productLineName]))
			$parentCategory = $this->parentCategories[$productLineName];
		else
			$parentCategory = Mage::getModel('catalog/category')->loadByAttribute('name', $parentCategoryName);

		// Check that we have parent category
		if (!$parentCategory || !$parentCategory->getId()) {
			// This is a parent category that does not exist yet, so we need
			// to create it with the path set to the store's (brand's) category
			if (!$parentCategory = $this->newParentCategory($row)) {
				$this->error('Unable to create parent category');
				return;
			}
		}

		// Since we may have just loaded or created a category, add it to the cache
		if (!isset($this->parentCategories[$productLineName]))
			$this->parentCategories[$productLineName] = $parentCategory;

		// Now we check out this category (product line) and make updates
		$category = Mage::getModel('catalog/category')->loadByAttribute('name', $productLineName);
		if (!$category || !$category->getId()) {
			// Category doesn't exist, needs to be created
			$category  = Mage::getModel('catalog/category');
			$_category = [
				'store_id' => self::ADMIN_STORE_ID,
				'path'	   => $parentCategory->getPath()
			];
		} else {
			// We already have this category, just perform updates
			$_category = [];
		}

		foreach ($this->fields as $field)
			$_category[$field] = $this->adapter->getMappedValue($field, $row, true);

		try {
	    	foreach ($_category as $key => $value)
	    		$category->setData($key, $value);

	    	$category->save();

	    	// Save image to global also
	    	// Mage::app()->setCurrentStore(0);
	    	// $category = Mage::getModel('catalog/category')->loadByAttribute('name', $name);
	    	// $category->setData('image', $_category['image'])->save();

	    	$this->debug('Category imported successfully - ' . $category->getName());
	    } catch (Exception $e) {
	    	$this->error($e->getMessage());
	    }
	}

	public function newParentCategory($row)
	{
		$category  = Mage::getModel('catalog/category');

		$_category = [
			'store_id' => self::ADMIN_STORE_ID,
			'path'	   => $this->storeCategoryPath
		];

		foreach ($this->fields as $field)
			$_category[$field] = $this->adapter->getMappedValue($field, $row, false);

		try {
	    	foreach ($_category as $key => $value)
	    		$category->setData($key, $value);

	    	$category->save();

	    	// Save image to global also
	    	// Mage::app()->setCurrentStore(0);
	    	// $category = Mage::getModel('catalog/category')->loadByAttribute('name', $name);
	    	// $category->setData('image', $_category['image'])->save();

	    	$this->debug('Category imported successfully - ' . $category->getName());

	    	return $category;
	    } catch (Exception $e) {
	    	$this->error($e->getMessage());
	    	return false;
	    }
	}

	public $MMYBaseCategory = false;
	public function getMMYBaseCategory()
	{
		if ($this->MMYBaseCategory)
			return $this->MMYBaseCategory;
		if (!$this->MMYBaseCategory = Mage::getModel('catalog/category')->loadByAttribute('name', 'MMY'))
			if (!$this->createMMYBaseCategory())
				return $this->error('Could not load MMY base category');

		return $this->MMYBaseCategory;
	}

	public function createMMYBaseCategory()
	{
		$category  = Mage::getModel('catalog/category');
		$_category = [
			'store_id'          => self::ADMIN_STORE_ID,
			'path'              => '1/' . self::DEFAULT_CATEGORY_ID,
			'name'              => 'MMY',
			'description'       => 'Year Make Model',
			'is_active'         => 1,
			'is_anchor'         => 1,
			'page_title'        => 'Year Make Model',
			'include_in_menu'   => 0,
			'display_mode'      => 'PAGE',
			'short_description' => 'Year Make Model',
		];
		try {
	    	foreach ($_category as $key => $value)
	    		$category->setData($key, $value);

	    	$category->save();
	    	$this->debug('MMY base category created successfully');

	    	// Put in cache
	    	$this->MMYBaseCategory = $category;
	    	return true;
	    } catch (Exception $e) {
	    	$this->error($e->getMessage());
	    	return false;
	    }
	}

	public $MMY = [];
	public function getMMYCategory($MMY)
	{
		// Check cache for category
		if (!isset($this->MMY[$MMY['make']]))
			if (!$this->cacheMakeCategory($MMY['make']))
				return $this->error('Could not create Make category - ' . implode(' - ', $MMY));

		if (!isset($this->MMY[$MMY['make']]['subcategories'][$MMY['model']]))
			if (!$this->cacheModelCategory($MMY['model'], $MMY['make']))
				return $this->error('Could not create Model category - ' . implode(' - ', $MMY));

		if (!isset($this->MMY[$MMY['make']]['subcategories'][$MMY['model']]['subcategories'][$MMY['year']]))
			if (!$this->cacheYearCategory($MMY['year'], $MMY['model'], $MMY['make']))
				return $this->error('Could not create Year category - ' . implode(' - ', $MMY));
		
		return $this->MMY[$MMY['make']]['subcategories'][$MMY['model']]['subcategories'][$MMY['year']];
	}

	public function cacheMakeCategory($make)
	{
		// See if we have this make in the MMY Base
		$like     = '1/2/' . $this->getMMYBaseCategory()->getId() . '/%';
		$category = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToFilter('name', $make)
					->addAttributeToFilter('path', ['like' => $like])
					->getFirstItem();

		if (!$category || !$category->getId()) {
			// If we can't find the category we need to create it
			$category  = Mage::getModel('catalog/category');
			$_category = [
				'store_id'          => self::ADMIN_STORE_ID,
				'path'              => $this->getMMYBaseCategory()->getPath(),
				'name'              => $make,
				'description'       => $make . ' Vehicles',
				'is_active'         => 1,
				'is_anchor'         => 1,
				'page_title'        => $make . ' Vehicles',
				'include_in_menu'   => 0,
				'display_mode'      => 'PRODUCTS',
				'short_description' => $make . ' Vehicles',
			];
			try {
		    	foreach ($_category as $key => $value)
		    		$category->setData($key, $value);

		    	$category->save();
		    	$this->debug('Make category created successfully - ' . $make);

		    	// Create bucket for make in cache
		    	$this->MMY[$make] = [
					'category'      => $category,
					'subcategories' => []
		    	];
		    	return true;
		    } catch (Exception $e) {
		    	$this->error($e->getMessage());
		    	return false;
		    }
		} else {
			$this->MMY[$make] = [
				'category'      => $category,
				'subcategories' => []
	    	];
	    	return true;
		}
	}

	public function cacheModelCategory($model, $make)
	{
		// See if we have this make in the MMY Base
		$like     = '1/2/' . $this->getMMYBaseCategory()->getId() . '/' . $this->MMY[$make]['category']->getId() . '/%';
		$category = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToFilter('name', $model)
					->addAttributeToFilter('path', ['like' => $like])
					->getFirstItem();
		if (!$category || !$category->getId()) {
			$makeModel = $make . ' ' . $model;
			// If we can't find the category we need to create it
			$category  = Mage::getModel('catalog/category');
			$_category = [
				'store_id'          => self::ADMIN_STORE_ID,
				'path'              => $this->MMY[$make]['category']->getPath(),
				'name'              => $model,
				'description'       => $makeModel . ' Vehicles',
				'is_active'         => 1,
				'is_anchor'         => 1,
				'page_title'        => $makeModel . ' Vehicles',
				'include_in_menu'   => 0,
				'display_mode'      => 'PRODUCTS',
				'short_description' => $makeModel . ' Vehicles',
			];
			try {
		    	foreach ($_category as $key => $value)
		    		$category->setData($key, $value);

		    	$category->save();
		    	$this->debug('Make Model category created successfully - ' . $makeModel);

		    	// Create bucket for make in cache
		    	$this->MMY[$make]['subcategories'][$model] = [
					'category'      => $category,
					'subcategories' => []
		    	];
		    	return true;
		    } catch (Exception $e) {
		    	$this->error($e->getMessage());
		    	return false;
		    }
		} else {
			$this->MMY[$make]['subcategories'][$model] = [
				'category'      => $category,
				'subcategories' => []
	    	];
	    	return true;
		}
	}

	public function cacheYearCategory($year, $model, $make)
	{
		// See if we have this make in the MMY Base
		$like     = '1/2/' . $this->getMMYBaseCategory()->getId() . '/' . $this->MMY[$make]['category']->getId() . '/' 
				. $this->MMY[$make]['subcategories'][$model]['category']->getId() . '/%';
		$category = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToFilter('name', $year)
					->addAttributeToFilter('path', ['like' => $like])
					->getFirstItem();
		if (!$category || !$category->getId()) {
			$makeModelYear = $make . ' ' . $model . ' ' . $year;
			// If we can't find the category we need to create it
			$category  = Mage::getModel('catalog/category');
			$_category = [
				'store_id'          => self::ADMIN_STORE_ID,
				'path'              => $this->MMY[$make]['subcategories'][$model]['category']->getPath(),
				'name'              => $year,
				'description'       => $makeModelYear . ' Vehicles',
				'is_active'         => 1,
				'is_anchor'         => 1,
				'page_title'        => $makeModelYear . ' Vehicles',
				'include_in_menu'   => 0,
				'display_mode'      => 'PRODUCTS',
				'short_description' => $makeModelYear . ' Vehicles',
			];
			try {
		    	foreach ($_category as $key => $value)
		    		$category->setData($key, $value);

		    	$category->save();
		    	$this->debug('Make Model Year category created successfully - ' . $makeModelYear);

		    	// Create bucket for make in cache
		    	$this->MMY[$make]['subcategories'][$model]['subcategories'][$year] = $category;
		    	return true;
		    } catch (Exception $e) {
		    	$this->error($e->getMessage());
		    	return false;
		    }
		} else {
			$this->MMY[$make]['subcategories'][$model]['subcategories'][$year] = $category;
			return true;
		}
	}
}