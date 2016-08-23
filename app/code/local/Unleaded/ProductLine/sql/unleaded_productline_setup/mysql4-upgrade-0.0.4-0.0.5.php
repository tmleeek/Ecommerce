<?php

$installer = $this;

$installer->startSetup();

$this->addAttribute('catalog_category', 'product_category_short_code', array(
	'group'            => 'General Information',
	'input'            => 'text',
	'type'             => 'text',
	'label'            => 'Product Category Short Code',
	'visible'          => true,
	'required'         => false,
	'visible_on_front' => true,
	'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();