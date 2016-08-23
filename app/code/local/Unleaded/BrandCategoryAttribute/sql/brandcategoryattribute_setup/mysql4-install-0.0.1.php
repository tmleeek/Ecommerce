<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_brands', array(
    'group'            => 'General Information',
    'input'            => 'multiselect',
    'type'             => 'varchar',
    'label'            => 'Brand(s)',
    'source'           => 'unleaded_brandcategoryattribute/attribute_source_brand',
    'backend'          => 'eav/entity_attribute_backend_array',
    'visible'          => true,
    'required'         => false,
    'visible_on_front' => true,
    'is_user_defined'  => true,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
 
$installer->endSetup();