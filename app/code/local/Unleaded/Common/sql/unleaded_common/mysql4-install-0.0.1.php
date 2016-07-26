<?php
$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description', array(
   'group'         => 'General Information',
   'input'         => 'textarea',
   'type'          => 'text',
   'label'         => 'Short Description',
   'backend'       => 'catalog/category_attribute_backend_image',
   'visible'       => true,
   'required'      => false,
   'visible_on_front' => true,
   'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$this->updateAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description', 'is_wysiwyg_enabled', 1);
$this->updateAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description', 'is_html_allowed_on_front', 1);
$this->endSetup();