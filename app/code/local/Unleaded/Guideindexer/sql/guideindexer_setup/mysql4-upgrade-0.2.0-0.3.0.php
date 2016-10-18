<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'i_sheet_downloads', array(
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'I Sheet Downloads Count',
    'input' => 'text',
    'class' => '',
    'source' => 'catalog/product_attribute_source_layout',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'group' => 'PIMS Data'
));

$installer->endSetup();
