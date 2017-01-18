<?php 

class Unleaded_PIMS_Helper_Import extends Unleaded_PIMS_Helper_Data
{
	const ADMIN_STORE_ID = 0;
	const LINE_LENGTH    = 10000;
	const DELIMITER      = ',';

	const PIMS_DIRECTORY = 'pims';

	const PARTS_PENDING_CSV_DIR  = 'pims/parts/pending/';
	const PARTS_ACTIVE_CSV_DIR   = 'pims/parts/active/';
	const PARTS_REJECTED_CSV_DIR = 'pims/parts/rejected/';

	const BRANDS_PENDING_CSV_DIR    = 'pims/brands/pending/';
	const BRANDS_ACTIVE_CSV_DIR     = 'pims/brands/active/';
	const BRANDS_REJECTED_CSV_DIR   = 'pims/brands/rejected/';

	const ROLLBACK_DIR = 'pims/rollbacks/';

	public $handle;
	public $headers;
	public $entity;
	public $connection;

	public $errors = [];
	public $info   = [];

	public $brandsFile;
	public $brandsHandle;
	public $brandsHeaders;

	public $partsFile;
	public $partsHandle;
	public $partsHeaders;

    public $storeCategories = [];

	private $canImport;
	public $count = 0;

	public function __construct()
	{
		// Necessities
		ini_set('memory_limit', '2048M');
		ini_set('max_execution_time', -1);
		ini_set('max_input_time', -1);
		set_time_limit(-1);

		$this->entity     = new Mage_Eav_Model_Entity_Setup('core_setup');
		$this->connection = $this->entity->getConnection('core_read');

		// Make sure our directories exist
		$this->checkDirectories();
	}

	private function checkDirectories()
	{
		$base = Mage::getBaseDir('var') . '/';

		// First check that the pims directory exists
		$fullPimsDir = $base . self::PIMS_DIRECTORY;
		if (!file_exists($fullPimsDir)) {
			mkdir($fullPimsDir, 0777, true);
			if (!file_exists($fullPimsDir))
				throw new Exception('Unabled to create PIMS directory');
		}

		// Now check each csv directory
		foreach ([
			self::PARTS_PENDING_CSV_DIR, self::PARTS_ACTIVE_CSV_DIR, self::PARTS_REJECTED_CSV_DIR,
			self::BRANDS_PENDING_CSV_DIR, self::BRANDS_ACTIVE_CSV_DIR, self::BRANDS_REJECTED_CSV_DIR, self::ROLLBACK_DIR
		] as $directory) {
			$fullDir = $base . $directory;
			if (!file_exists($fullDir)) {
				mkdir($fullDir, 0777, true);
				if (!file_exists($fullPimsDir))
					throw new Exception('Unabled to create directory: ' . $fullDir);
			}
		}
	}

	public function __destruct()
	{
		if ($this->partsHandle)
			fclose($this->partsHandle);
		if ($this->brandsHandle)
			fclose($this->brandsHandle);
	}

	public function setBrandsFile($fileName)
	{
		return $this->setFile($fileName, 'brands');
	}

	public function setPartsFile($fileName)
	{
		return $this->setFile($fileName, 'parts');		
	}

	public function setFile($fileName, $type)
	{
		$fileVar     = $type . 'File';
		$handleName  = $type . 'Handle';
		$headersName = $type . 'Headers';

		$this->$fileVar = Mage::getBaseDir('var') . '/';
		switch ($type) {
			case 'parts';
				$this->$fileVar .= self::PARTS_PENDING_CSV_DIR;
				break;
			case 'brands';
				$this->$fileVar .= self::BRANDS_PENDING_CSV_DIR;
				break;
			default;
				throw new Exception('File type not supported: ' . $type);
		}
		$this->$fileVar .= $fileName;

		if ($this->$handleName)
			fclose($this->$handleName);

		if (!$this->$handleName = fopen($this->$fileVar, 'r'))
			return $this->error('Could not open file');

		$this->$headersName = fgetcsv($this->$handleName, self::LINE_LENGTH, self::DELIMITER);

		return $this;
	}

	public function getUniqueSkuCount($store)
	{
		$skus = [];
		try {
			while (($_row = fgetcsv($this->partsHandle, self::LINE_LENGTH, self::DELIMITER)) !== false) {
				$row = array_combine($this->partsHeaders, $_row);
				$skus[] = Mage::helper('unleaded_pims/import_parts_adapter')->getMappedValue('sku', $row);
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());
			$this->error($e->getTraceAsString());
		}

		$skus = array_unique($skus);

		echo strtoupper($store) . ' has ' . count($skus) . ' unique SKUs in file:' . PHP_EOL 
			. $this->partsFile . PHP_EOL . PHP_EOL;

		return $this;
	}

	public function parts($store)
	{
		$importer = Mage::helper('unleaded_pims/import_parts');
		Mage::app()->setCurrentStore(self::ADMIN_STORE_ID);
        $importer->setStore($store);

        // Comment if you do not want to download images via ftp (saves a lot of time)
        $importer->setSaveWithImages(false);

        $storeCount = 0;
		try {
			while (($_row = fgetcsv($this->partsHandle, self::LINE_LENGTH, self::DELIMITER)) !== false) {
				/// Sku will be just part number and upc code
				// We need to grab every row, in order, that matches the s ku
				// This becomes one product
				// We then need to make sure that each vehicle configuration exists in the YMM
				// Then attach those vehicles to the product

				$row = array_combine($this->partsHeaders, $_row);
				// First get the sku
				$sku = Mage::helper('unleaded_pims/import_parts_adapter')->getMappedValue('sku', $row);
				// Check if this is a new sku
				if ($importer->isNewSku($sku)) {
					$this->debug('#' . ++$this->count . ' - ' . strtoupper($store) . ' Product #' . ++$storeCount);
					// If this is a new sku, we need to reference the next row because it has the data
					// But we need to save the vehicle data from this row because
					// this is the only place it exists
					if (!$_dataRow = fgetcsv($this->partsHandle, self::LINE_LENGTH, self::DELIMITER)) {
						// If we don't have another row, the import is complete
						break;
					}
					$dataRow  = array_combine($this->partsHeaders, $_dataRow);
					// We need to add this vehicle
					$importer->newProduct($sku, $dataRow, $row['Vehicle Type']);
					$importer->addVehicle(
						$dataRow['Year'], $dataRow['Make'], $dataRow['Model'],
						$dataRow['SubModel'], $dataRow['SubDetail']
					);
				// If this is not a new sku, but we do have a sku, then this is an additional
				// vehicle row and must be added to the product
				} else if ($importer->hasSku()) {
					$importer->addVehicle($row['Year'], $row['Make'], $row['Model'], $row['SubModel'], $row['SubDetail']);
				} else {
					// We should never hit this, the while loop should finish first
					throw new Exception('Not really sure how we got here but we are missing something somewhere');
				}
			}
			// Need to save the last product
			$importer->saveCurrentProduct();
		} catch (Exception $e) {
			$this->error($e->getMessage());
			$this->error($e->getTraceAsString());

			return $this;
		}

		Mage::helper('unleaded_pims/import_parts_configurables')->checkConfigurables();

		return $this;
	}

	public function brands($storeCode = 'admin')
	{
		$importer = Mage::helper('unleaded_pims/import_brands');
		
		if (!$importer->setStore($storeCode)) {
			$this->canImport = false;
			$this->error('Unable to load store with code ' . $storeCode);
			return $this;
		}

		try {
			while (($_row = fgetcsv($this->brandsHandle, self::LINE_LENGTH, self::DELIMITER)) !== false) {

				$row = array_combine($this->brandsHeaders, $_row);

				$importer->import($row);
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());

			return $this;
		}

		$importer->saveCategoryBrands();
		
		return $this;
	}

	public function getRollbackDir()
	{
		return Mage::getBaseDir('var') . '/' . self::ROLLBACK_DIR;		
	}
}