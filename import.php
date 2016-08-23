<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

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
	$importer
		->setProductFile('var/pims/product/MagentoParts_csv_avs_full_20160726033838.csv')
   		->products('avs')
   		->setProductFile('var/pims/product/MagentoParts_csv_lund_full_20160726034923.csv')
		->products('lund');
}

importCategories();
importProducts();