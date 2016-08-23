<?php 

class Unleaded_PIMS_Helper_Import extends Unleaded_PIMS_Helper_Data
{
	const ADMIN_STORE_ID = 0;
	const LINE_LENGTH    = 5000;
	const DELIMITER      = ',';

	public $handle;
	public $headers;
	public $entity;
	public $connection;

	public $errors = [];
	public $info   = [];

	public $categoryFile;
	public $categoryHandle;
	public $categoryHeaders;

	public $productFile;
	public $productHandle;
	public $productHeaders;

    public $storeCategories = [];

	private $canImport;

	public function __construct()
	{
		// Necessities
		ini_set('memory_limit', '2048M');
		ini_set('max_execution_time', -1);
		ini_set('max_input_time', -1);
		set_time_limit(-1);

		$this->entity     = new Mage_Eav_Model_Entity_Setup('core_setup');
		$this->connection = $this->entity->getConnection('core_read');
	}

	public function __destruct()
	{
		if ($this->productHandle)
			fclose($this->productHandle);
		if ($this->categoryHandle)
			fclose($this->categoryHandle);
	}

	public function setCategoryFile($fileName)
	{
		return $this->setFile($fileName, 'category');
	}

	public function setProductFile($fileName)
	{
		return $this->setFile($fileName, 'product');		
	}

	public function setFile($fileName, $type)
	{
		$fileVar     = $type . 'File';
		$handleName  = $type . 'Handle';
		$headersName = $type . 'Headers';

		$this->$fileVar = $fileName;

		if ($this->$handleName)
			fclose($this->$handleName);

		if (!$this->$handleName = fopen($this->$fileVar, 'r'))
			return $this->error('Could not open file');

		$this->$headersName = fgetcsv($this->$handleName, self::LINE_LENGTH, self::DELIMITER);

		return $this;
	}

	public function products($store)
	{

		$importer = Mage::helper('unleaded_pims/import_product');
		Mage::app()->setCurrentStore(self::ADMIN_STORE_ID);
        $importer->setStore($store);

		try {
			while (($_row = fgetcsv($this->productHandle, self::LINE_LENGTH, self::DELIMITER)) !== false) {
				/// Sku will be just part number and upc code
				// We need to grab every row, in order, that matches the s ku
				// This becomes one product
				// We then need to make sure that each vehicle configuration exists in the YMM
				// Then attach those vehicles to the product

				$row = array_combine($this->productHeaders, $_row);

				// First get the sku
				$sku = Mage::helper('unleaded_pims/import_product_adapter')->getMappedValue('sku', $row);
				// Check if this is a new sku
				if ($importer->isNewSku($sku)) {
					// If this is a new sku, we need to reference the next row because it has the data
					// But we need to save the vehicle data from this row because
					// this is the only place it exists
					$_dataRow = fgetcsv($this->productHandle, self::LINE_LENGTH, self::DELIMITER);
					$dataRow  = array_combine($this->productHeaders, $_dataRow);
					$importer->newProduct($sku, $dataRow, $row['Vehicle Type']);
				// If this is not a new sku, but we do have a sku, then this is an additional
				// vehicle row and must be added to the product
				} else if ($importer->hasSku()) {
					$importer->addVehicle($row['Year'], $row['Make'], $row['Model'], $row['SubModel'], $row['SubDetail']);
				} else {
					// We should never hit this, the while loop should finish first
					throw new Exception('Not really sure how we got here but we are missing something somewhere');
				}
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());
			$this->error($e->getTraceAsString());

			return $this;
		}

		return $this;
	}

	public function categories($storeCode = 'admin')
	{
		$importer = Mage::helper('unleaded_pims/import_category');
		
		if (!$importer->setStore($storeCode)) {
			$this->canImport = false;
			$this->error('Unable to load store with code ' . $storeCode);
			return $this;
		}

		try {
			while (($_row = fgetcsv($this->categoryHandle, self::LINE_LENGTH, self::DELIMITER)) !== false) {

				$row = array_combine($this->categoryHeaders, $_row);

				$importer->import($row);
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());

			return $this;
		}

		$importer->saveCategoryBrands();
		
		return $this;
	}
}