<?php

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

// We need to turn these attributes into multiselects
$attributes = [
	// These are the configurable attributes we want to concatenate
	'country_of_manufacture' => 'Origin',
];

foreach ($attributes as $attributeCode => $label) {
	$attribute = $installer->getAttribute($entityTypeId, $attributeCode);

	Mage::log('Removing attribute code ' . $attributeCode . ' with id ' . $attribute['attribute_id']);

	$installer->removeAttribute($entityTypeId, $attributeCode);

	Mage::log('Adding attribute ' . $attributeCode);	
	$installer->addAttribute($entityTypeId, $attributeCode, [
		'type'                          => 'text',
		'backend'                       => '',
		'frontend'                      => '',
		'label'                         => $label,
		'input'                         => 'multiselect',
		'class'                         => '',
		'source'                        => 'eav/entity_attribute_source_table',
		'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'                       => true,
		'required'                      => false,
		'user_defined'                  => false,
		'default'                       => '',
		'visible_on_front'              => true,
		'unique'                        => false,
		'group'                         => 'PIMS Data'
	]);
}

// These attribute options aren't in the addAttribute API
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