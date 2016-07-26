<?php
$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description_featured', array(
   'group'         => 'General Information',
   'input'         => 'textarea',
   'type'          => 'text',
   'label'         => 'Featured Short Description',
   'backend'       => 'catalog/category_attribute_backend_image',
   'visible'       => true,
   'required'      => false,
   'visible_on_front' => true,
   'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$this->updateAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description_featured', 'is_wysiwyg_enabled', 1);
$this->updateAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description_featured', 'is_html_allowed_on_front', 1);

$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'featured_title', array(
   'group'         => 'General Information',
   'input'         => 'text',
   'type'          => 'text',
   'label'         => 'Featured Title',
   'backend'       => 'catalog/category_attribute_backend_image',
   'visible'       => true,
   'required'      => false,
   'visible_on_front' => true,
   'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'featured_image', array(
    'group'         => 'General Information',
    'input'         => 'image',
    'type'          => 'varchar',
    'label'         => 'Featured Image',
    'backend'       => 'catalog/category_attribute_backend_image',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->endSetup();