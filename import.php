<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

function deleteAllProducts() {
	$totalProductCount = Mage::getModel('catalog/product')->getCollection()->getSize();

	$productCount = 1;
	for ($batch = 1, $totalBatches = ceil($totalProductCount / 1000); $batch <= $totalBatches; $batch++) {
		$products = Mage::getModel("catalog/product")
					->getCollection()
					->setPageSize(1000)
					->setCurPage($batch);
		
		foreach ($products as $product) {
			try {
				$product->delete();
			} catch (Exception $e) {
				echo $e->getMessage() . PHP_EOL;
			}
		}
	}
}

function importCategories() {
	$importer = Mage::helper('unleaded_pims/import');
	$importer
		->setCategoryFile('var/pims/category/MagentoBrands_csv_avs_full_20160718054659.csv')
		->categories('avs')
		->setCategoryFile('var/pims/category/MagentoBrands_csv_lund_full_20160705014726.csv')
		->categories('lund');
}

function importProducts() {
	$importer = Mage::helper('unleaded_pims/import');

	// Testing with truncated csv
	// $importer->setProductFile('var/pims/product/avs_truncated.csv')->products('avs');return;

	$importer
		->setProductFile('var/pims/product/MagentoParts_csv_avs_full_20160726033838.csv')
		->products('avs')
		->setProductFile('var/pims/product/MagentoParts_csv_lund_full_20160726034923.csv')
		->products('lund');
}

function checkConfigurables() {
	$importer = Mage::helper('unleaded_pims/import_product_configurables');
	$importer->checkConfigurables();
}

function getUniqueSkuCounts() {
	$importer = Mage::helper('unleaded_pims/import');
	$importer
		->setProductFile('var/pims/product/MagentoParts_csv_avs_full_20160726033838.csv')
		->getUniqueSkuCount('avs')
		->setProductFile('var/pims/product/MagentoParts_csv_lund_full_20160726034923.csv')
		->getUniqueSkuCount('lund');
}

// deleteAllProducts();
// importCategories();
importProducts();
// checkConfigurables();
// getUniqueSkuCounts();