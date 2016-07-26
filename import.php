<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

$importer = Mage::helper('unleaded_pims/import');

// $categoryCollection = Mage::getModel('catalog/category')->getCollection();
// foreach ($categoryCollection as $category) {
// 	if (in_array($category->getId(), [1,2]))
// 		continue;
// 	$category->delete();
// }


$importer->setFile('var/pims/category/MagentoBrands_csv_avs_full_20160705014726.csv');
$importer->categories('avs');
$importer->setFile('var/pims/category/MagentoBrands_csv_lund_full_20160705014726.csv');
$importer->categories('lund');

// $importer->setFile('var/pims/product/truncated2.csv');
// $importer->products('avs');