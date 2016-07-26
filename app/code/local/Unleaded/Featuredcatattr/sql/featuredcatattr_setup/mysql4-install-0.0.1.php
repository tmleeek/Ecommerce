<?php
$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'is_featured_category', array(
    'group'         => 'General Information',
    'input'         => 'select',
    'type'          => 'int',
    'label'         => 'Featured Category',
    'source'        => 'eav/entity_attribute_source_boolean',
    'backend'       => 'catalog/category_attribute_backend_image',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
 
$this->endSetup();