<?php
require('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(1);

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', -1);
ini_set('max_input_time', -1);
set_time_limit(-1);

function go($output) {
	$handle = fopen('nextopia-feed.tsv', 'w');

	$data = [
		'id'            => '',
		'title'         => '',
		'description'   => '',
		'item_group_id' => '',
		'price'         => '',
		'link'          => '',
		'image_link'    => '',
		'product_type'  => ''
	];

	fputcsv($handle, array_keys($data), "\t");

	$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');

	$baseUrl = Mage::getBaseUrl();

	foreach ($productCollection as $product) {
		$categoryIds = $_product->getCategoryIds();

        if (count($categoryIds)) {
			$category  = Mage::getModel('catalog/category')->load($categoryIds[0]);
			$_category = $category->getName();
        } else {
        	$category = '';
        }

		$data['id']            = $product->getId();
		$data['title']         = $product->getName();
		$data['description']   = $product->getDescription();
		$data['item_group_id'] = $product->getAttributeSetId();
		$data['price']         = $product->getPrice();
		$data['link']          = $baseUrl . $product->getUrlPath();
		$data['image_link']    = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
		$data['product_type']  = $category;

		fputcsv($handle, array_values($data), "\t");
	}
	fclose($handle);
}

go(true);