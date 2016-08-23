<?php

$installer = $this;

$installer->startSetup();

// Remove old product line attributes
foreach ([
	'product_line_install_video', 'product_line_features',
	'product_line_v01_video', 'product_line_v02_video', 'product_line_v03_video', 
	'product_line_v04_video', 'product_line_v05_video', 'product_line_v06_video'
] as $attributeCode) {
	$installer->removeAttribute('catalog_product', $attributeCode);
}

// Add product line id attribute
$installer->addAttribute('catalog_product', 'product_line', [
	'group'                   => 'PIMS Data',
	'label'                   => 'Product Line',
	'note'                    => '',
	'type'                    => 'int',	//backend_type
	'input'                   => 'select',	//frontend_input
	'frontend_class'          => '',
	'source'                  => 'unleaded_productline/source_attribute',
	'backend'                 => '',
	'frontend'                => '',
	'global'                  => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'required'                => true,
	'visible_on_front'        => true,
	'apply_to'                => ['simple', 'configurable'],
	'is_configurable'         => false,
	'used_in_product_listing' => true,
	'sort_order'              => 5,
]);

$installer->endSetup();