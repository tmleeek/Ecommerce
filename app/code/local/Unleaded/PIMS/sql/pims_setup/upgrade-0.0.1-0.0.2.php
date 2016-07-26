<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

////////////////////////////
/// Attributes setup
////////////////////////////
require('attributeMap-0.0.2.php');
foreach ($attributesToAdd as $attribute) {
	$_attribute = array(
		'attribute_code'                => $attribute['code'],
		'is_global'                     => '1',
		'frontend_input'                => $attribute['input'],
		'is_unique'                     => '0',
		'is_required'                   => '0',
		'is_configurable'               => '0',
		'is_searchable'                 => $attribute['is_searchable'],
		'is_visible_in_advanced_search' => $attribute['is_visible_in_advanced_search'],
		'is_comparable'                 => $attribute['is_comparable'],
		'is_used_for_price_rules'       => '0',
		'is_wysiwyg_enabled'            => '0',
		'is_html_allowed_on_front'      => '1',
		'is_visible_on_front'           => $attribute['is_visible_on_front'],
		'used_in_product_listing'       => $attribute['used_in_product_listing'],
		'used_for_sort_by'              => $attribute['used_for_sort_by'],
		'frontend_label'                => $attribute['label'],
		'is_filterable'                 => $attribute['is_filterable'],
		'is_filterable_in_search' 	    => $attribute['is_filterable_in_search']
	);

	$model = Mage::getModel('catalog/resource_eav_attribute');

	if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0)
		$_attribute['backend_type'] = $model->getBackendTypeByInput($_attribute['frontend_input']);
	
	$model
		->setEntityTypeId($entityTypeId)
		->addData($_attribute)
		->setIsUserDefined(1);

	try {
		$model->save();
	} catch (Exception $e) {
		Mage::logException($e);
	}
}
////////////////////////////
/// END Attributes setup
////////////////////////////

////////////////////////////
/// Add new attributes to all sets
////////////////////////////
$attributeSetEntityType	= Mage::getModel('eav/entity_type')
							->getCollection()
							->addFieldToFilter('entity_type_code','catalog_product')
							->getFirstItem();

$attributeSetCollection = $attributeSetEntityType->getAttributeSetCollection();

foreach ($attributesToAdd as $attribute) {
	$attribute = Mage::getResourceModel('eav/entity_attribute_collection')
					->setCodeFilter($attribute['code'])
					->getFirstItem();

	foreach ($attributeSetCollection as $attributeSet) {
		$group = Mage::getModel('eav/entity_attribute_group')
					->getCollection()
					->addFieldToFilter('attribute_set_id', $attributeSet->getId())
					->addFieldToFilter('attribute_group_name', 'PIMS Data')
					->getFirstItem();

		$newItem = Mage::getModel('eav/entity_attribute');
		$newItem
			->setEntityTypeId($attributeSetEntityType->getId())
			->setAttributeSetId($attributeSet->getId())
			->setAttributeGroupId($group->getId())
			->setAttributeId($attribute->getId())
			->setSortOrder(10)
			->save();
	}
}
////////////////////////////
/// END Add new attributes to all sets
////////////////////////////

$installer->endSetup();