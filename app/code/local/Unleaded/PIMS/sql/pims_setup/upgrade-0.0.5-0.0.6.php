<?php

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

// We need to turn these attributes into multiselects
$attributes = [
	// These are the configurable attributes we want to concatenate
	'bed_length',
	'bed_type',
	'flare_height',
	'flare_tire_coverage', 
	'box_style',
	'box_opening',
	'color',
	'finish',
	'style',
	'material',
	'material_thickness', 
	'sold_as',
	'tube_shape',
	'tube_size',
	'liquid_storage_capacity',
	// These are additional attributes we want to concatenate
	'pop_code',
	'brand_short_code',
	'i_sheet',
	'model_type',
	'vehicle_type',
	'height',
	'width',
	'length'
];

$settings = [
	'is_filterable',
	'is_searchable',
	'is_comparable',
	'is_visible_in_advanced_search',
	'is_filterable_in_search'
];

foreach ($attributes as $attributeCode) {
	foreach ($settings as $setting) {
		$installer->updateAttribute('catalog_product', $attributeCode, $setting, 1);
	}
}

$installer->endSetup();