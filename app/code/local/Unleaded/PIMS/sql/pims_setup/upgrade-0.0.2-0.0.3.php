<?php
// http://inchoo.net/magento/how-to-add-new-custom-category-attribute-in-magento/
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');

$installer->addAttribute('catalog_category', 'product_category_display_name',  array(
	'type'         => 'text',
	'label'        => 'Product Category Display Name',
	'input'        => 'text',
	'group'		   => 'General Information',
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'      => true,
	'required'     => false,
	'user_defined' => true,
	'default'      => null
));

$installer->endSetup();