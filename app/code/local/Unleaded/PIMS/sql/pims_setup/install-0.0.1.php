<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

////////////////////////////
/// Attributes setup
////////////////////////////
require('attributeMap-0.0.1.php');
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
/// Attribute Sets setup
////////////////////////////
require('attributeSetMap-0.0.1.php');

// First we need to process the default group
$groupName             = 'PIMS Data';
$defaultAttributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);

// Add PIMS Data group to default attribute set
$installer->addAttributeGroup($entityTypeId, $defaultAttributeSetId, $groupName, 100);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $defaultAttributeSetId, $groupName);

// Add attributes to PIMS Data Group in default attribute set
foreach ($addToDefault as $attributeCode) {
	$attributeId = $installer->getAttributeId($entityTypeId, $attributeCode);
	$installer->addAttributeToGroup($entityTypeId, $defaultAttributeSetId, $attributeGroupId, $attributeId, null);
}

// Now process new attribute groups using default as skeleton
foreach ($attributeSets as $attributeSet) {
	$model = Mage::getModel('eav/entity_attribute_set')
				->getCollection()
				->setEntityTypeFilter($entityTypeId)
				->addFieldToFilter('attribute_set_name', $attributeSet['label'])
				->getFirstItem();

	if (!is_object($model))
		$model = Mage::getModel('eav/entity_attribute_set');

	if (!is_numeric($model->getAttributeSetId()))
		$new = true;

	$model
		->setEntityTypeId($entityTypeId)
		->setAttributeSetName($attributeSet['label'])
		->validate();

	try {
		$model->save();

		if ($new)
			$model->initFromSkeleton($defaultAttributeSetId)->save();

	} catch (Exception $e) { 
		Mage::logException($e);
	}

	// Add to pims group in this attribute set
	$group = Mage::getModel('eav/entity_attribute_group')
				->getResourceCollection()
				->addFieldToFilter('attribute_group_name', $groupName)
				->setAttributeSetFilter($model->getId())
				->getFirstItem();
	// Now we add attributes to this set
	foreach ($attributeSet['attributesToAdd'] as $attributeCode) {
		$attributeId = $installer->getAttributeId($entityTypeId, $attributeCode);
		try {
			$installer->addAttributeToGroup($entityTypeId, $model->getId(), $group->getId(), $attributeId, null);
		} catch (Exception $e) {
			var_dump($model->getId());
			var_dump($attributeGroupId);
			var_dump($attributeId);exit;
		}
	}
}

$installer->endSetup();