<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'compatible_vehicles',array(
    'group' => 'General',
    'label' => 'Compatible Vehicles',
    'note' => 'Select Vehicles Compatibility For This Product',
    'type' => 'varchar', //backend_type
    'input' => 'multiselect', //frontend_input
    'frontend_class' => '',
    'source' => 'vehicle/source_vehicle',
    'backend' => '',
    'frontend' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'required' => FALSE,
    'visible_on_front' => TRUE,
    'backend_model' => 'eav/entity_attribute_backend_array',
    'visible' => true,
    'user_defined' => true,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
));

$installer->endSetup();
