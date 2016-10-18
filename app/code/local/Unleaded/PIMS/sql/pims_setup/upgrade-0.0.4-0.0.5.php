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

foreach ($attributes as $attributeCode) {
	$attribute = $installer->getAttribute($entityTypeId, $attributeCode);

	Mage::log('Removing attribute code ' . $attributeCode . ' with id ' . $attribute['attribute_id']);

	$installer->removeAttribute($entityTypeId, $attributeCode);

	Mage::log('Adding attribute ' . $attributeCode);	
	$installer->addAttribute($entityTypeId, $attributeCode, [
		'type'                          => 'text',
		'backend'                       => '',
		'frontend'                      => '',
		'label'                         => ucwords(str_replace('_', ' ', $attributeCode)),
		'input'                         => 'multiselect',
		'class'                         => '',
		// 'source'                     => 'catalog/product_attribute_source_layout',
		'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'                       => true,
		'required'                      => false,
		'user_defined'                  => false,
		'default'                       => '',
		'is_searchable'                 => true,
		'is_filterable'                 => Mage_Catalog_Model_Layer_Filter_Attribute::OPTIONS_ONLY_WITH_RESULTS,
		'is_filterable_in_search'       => true,
		'is_comparable'                 => true,
		'visible_on_front'              => true,
		'is_visible_in_advanced_search' => true,
		'unique'                        => false,
		'group'                         => 'PIMS Data'
	]);
}

// This next part will clean up all of the abandoned eav rows
$tables = [
	'catalog_product_entity_datetime',
	'catalog_product_entity_decimal',
	'catalog_product_entity_gallery',
	'catalog_product_entity_int',
	'catalog_product_entity_media_gallery',
	'catalog_product_entity_media_gallery_value',
	'catalog_product_entity_text',
	'catalog_product_entity_url_key',
	'catalog_product_entity_varchar'
];
foreach ($tables as $table) {
	$query = 'DELETE a.* FROM ' . $table . ' a NATURAL LEFT JOIN eav_attribute b WHERE b.attribute_id IS NULL;';
	$installer->run($query);
}

$installer->endSetup();