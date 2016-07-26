<?php

include ('app/Mage.php');
Mage::app();
Mage::setIsDeveloperMode(true);
Mage::register('isSecureArea', true);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

umask(0);

$map = [
	// AVS
	'Hood Protection' => [
		'thumbnail' => 'AVS Hood Protection_127x90.jpg',
		'hero'      => 'AVS Hood Protection_category banner.jpg',
	],
	'Interior Accessories' => [
		'thumbnail' => 'AVS Interior Accessories_127x90.jpg',
		'hero'      => 'AVS Interior Accessories_category banner.jpg',
	],
	'Light Covers' => [
		'thumbnail' => 'AVS Light Covers_127x90.jpg',
		'hero'      => 'Light Covers_category banner.jpg',
	],
	'Ventvisors / Side Window Deflectors' => [
		'thumbnail' =>'AVS Ventvisors_127x90.jpg',
		'hero'      => 'AVS Ventvisor_side window deflector_category banner.jpg',
	],
	'Exterior Accessories' => [
		'thumbnail' => 'Exterior Chrome Accessories_127x90.jpg',
		'hero'      => 'Exterior Accessories_Chrome_category banner.jpg',
	],
	'LUND LOOK' => [
		'thumbnail' => false,
		'hero'      => false
	],
	'MISCELLANEOUS' => [
		'thumbnail' => false,
		'hero'      => false
	],
	'Hood Scoops' => [
		'thumbnail' => 'AVS Scoops_127x90.jpg',
		'hero'      => 'Scoops_vents_category banner.jpg',
	],
	'COMBO KITS' => [
		'thumbnail' => 'Combo_Kits_Matte.jpg',
		'hero'      => 'AVS combo kits_category banner.jpg',
	],
	// Lund
	'Running Boards' => [
		'thumbnail' => 'Lund Running Boards_127x90.jpg',
		'hero'      => 'LUND Running Boards_category banner.jpg',
	],
	'Fender Flare Elite Series' => [
		'thumbnail' => 'Lund Fender Flares127x90.jpg',
		'hero'      => 'LUND Elite Series Fender Flares_category banner.jpg',
	],
	'Jeep Fender Flares' => [
		'thumbnail' => false,
		'hero'      => 'LUND Jeep Fender Flares_category banner.jpg',
	],
	'Bed Accessories' => [
		'thumbnail' => false,
		'hero'      => 'Lund Bed Accessories_127x90.jpg',
	],
	'Floor Liners' => [
		'thumbnail' => 'Lund Flooring_127x90.jpg',
		'hero'      => 'LUND Floor Liners_category banner.jpg',
	],
	'Storage Boxes' => [
		'thumbnail' => 'Lund Storage Boxes_127x90.jpg',
		'hero'      => 'LUND Storage Boxes_category banner.jpg',
	],
	'Nerf Bars' => [
		'thumbnail' => 'Lund Nerf Bars_127x90.jpg',
		'hero'      => 'LUND Nerf Bars_category banner.jpg',
	],
	'Air Deflectors' => [
		'thumbnail' => 'Combo_Kits_Matte.jpg',
		'hero'      => 'LUND Air Deflectors_category banner.jpg',
	],
	'CONTRACTOR BOX TONNEAU SYSTEM' => [
		'thumbnail' => false,
		'hero'      => 'AVS combo kits_category banner.jpg',
	],
	'Liquid Storage Tanks' => [
		'thumbnail' => 'Lund Liquid Storage Tanks_127x90.jpg',
		'hero'      => 'LUND Liquid Storage Tanks_category banner.jpg',
	],
	'Cargo Management' => [
		'thumbnail' => 'Lund Cargo Management_127x90.jpg',
		'hero'      => 'LUND Cargo Management_category banner.jpg',
	],
	'Grille Coverings' => [
		'thumbnail' => 'Lund Grilles_127x90.jpg',
		'hero'      => 'LUND Grille Coverings_category banner.jpg',
	],
	'Tonneau Covers' => [
		'thumbnail' => 'Tonneau Covers_127x90.jpg',
		'hero'      => 'LUND Tonneau Covers_category banner.jpg',
	],
	'Bull Bars' => [
		'thumbnail' => 'Lund Bull Bars_127x90.jpg',
		'hero'      => 'LUND Bull Bar_category banner.jpg',
	],
	'Rhino Linings® Rocker Guards' => [
		'thumbnail' => 'Rhino Lining Panel Guards_127x90.jpg',
		'hero'      => 'LUND Rhino Rocker Guards_category banner.jpg',
	],
	// 'LUND Combo Kits_category banner.jpg',
	// 'LUND Hood Protection_category banner.jpg',
	// 'LUND Misc_category banner.jpg',
	// 'LUND Ventvisor_SWD1_category banner.jpg',
	// 'Lund Hood Protection_127x90.jpg',
	// 'Lund Ventvisors_127x90.jpg',
	// 'Matte Combo Kits_127x90.jpg',
	// 'Nerf-bar.jpg',
	// 'Textured Combo Kits_127x90.jpg',
	// 'Textured_Combo_Kit.jpg',
];

foreach ($map as $categoryName => $data) {
	$category = Mage::getModel('catalog/category')->loadByAttribute('name', $categoryName);

	if (!$localPath = Mage::helper('unleaded_pims/ftp')->getCategoryImage($data['hero'])) {
		echo 'Couldn\'t get hero image' . PHP_EOL;
	}
	if (!$localPath = Mage::helper('unleaded_pims/ftp')->getCategoryImage($data['thumbnail'])) {
		echo 'Couldn\'t get thumbnail image' . PHP_EOL;
	}

	$category
		->setImage($data['hero'])
		->setThumbnail($data['thumbnail'])
		->save();

}