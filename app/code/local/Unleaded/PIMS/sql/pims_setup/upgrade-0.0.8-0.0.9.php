<?php

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

// We need to make these not filterable
$attributes = [
	'bed_length',
	'bed_type',
	'flare_height',
	'flare_tire_coverage', 
	'box_style',
	'box_opening',
	'finish',
	'style',
	'material',
	'material_thickness', 
	'sold_as',
	'tube_shape',
	'tube_size',
	'liquid_storage_capacity',
	'pop_code',
	'i_sheet',
	'model_type',
	'vehicle_type',
	'height',
	'width',
	'length',
	'price',
	'brand_short_code'
];

foreach ($attributes as $attributeCode) {
	$installer->updateAttribute('catalog_product', $attributeCode, 'is_filterable', 0);
}

$installer->endSetup();