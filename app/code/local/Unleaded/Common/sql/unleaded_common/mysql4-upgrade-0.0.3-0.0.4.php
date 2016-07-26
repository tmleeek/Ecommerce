<?php
$this->startSetup();

$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'small_image', array(
    'group'         => 'General Information',
    'input'         => 'image',
    'type'          => 'varchar',
    'label'         => 'Small Image',
    'backend'       => 'catalog/category_attribute_backend_image',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$this->endSetup();