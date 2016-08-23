<?php

class Unleaded_PIMS_Helper_Import_ProductLine extends Unleaded_PIMS_Helper_Data
{	
	protected $fields = [
		'name',
		'description',
		'short_description',
		'product_line_short_code',
		'product_line_feature_benefits',
		'product_line_install_video',
		'product_line_v01_video',
		'product_line_v02_video',
		'product_line_v03_video',
		'product_line_v04_video',
		'product_line_v05_video',
		'product_line_v06_video'
	];

	public function __construct()
	{
		$this->adapter = Mage::helper('unleaded_pims/import_productline_adapter');
	}

	public function import($parentCategory, $row)
	{
		// First try to search for this product line
		$name = $this->adapter->getMappedValue('name', $row);

		$productLine = Mage::getModel('unleaded_productline/productline')
						->getCollection()
						->addFieldToFilter('name', $name)
						->getFirstItem();

		if (!$productLine || !$productLine->getId()) {
			// This needs to become a new product line
			$productLine = Mage::getModel('unleaded_productline/productline');
		}

		// Map the data
		$_productData = [];
		foreach ($this->fields as $field) {
			$_productData[$field] = $this->adapter->getMappedValue($field, $row);
		}

		// Set the data to the product line model
		foreach ($_productData as $key => $value) {
			$productLine->setData($key, $value);
		}

		// Save parent category
		$productLine->setParentCategoryId($parentCategory->getId());

		// Try to save it and report
		try {
			$productLine->save();
			$this->info('Product Line saved ' . $productLine->getName());
		} catch (Exception $e) {
			$this->error($e);
		}
	}
}