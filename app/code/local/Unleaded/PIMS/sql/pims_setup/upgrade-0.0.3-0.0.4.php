exit<?php
// http://inchoo.net/magento/how-to-add-new-custom-category-attribute-in-magento/
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');

$attributesToAdd = [
	'product_line_install_video' => [
		'type'  => 'text',
		'input' => 'text',
		'label' => 'Product Line Install Video'
	],
	'product_line_feature_benefits' => [
		'type'    => 'text',
		'input'   => 'textarea',
		'label'   => 'Product Line Features',
		'wysiwyg' => true
	]
];
foreach (['v01', 'v02', 'v03', 'v04', 'v05', 'v06'] as $add) {
	$attributesToAdd['product_line_' . $add . '_video'] = [
		'type'  => 'text',
		'input' => 'text',
		'label' => 'Product Line ' . strtoupper($add) . ' Video'
	];
}

foreach ($attributesToAdd as $attributeCode => $data) {
	$installer->addAttribute($entityTypeId, $attributeCode,  array(
		'type'             => $data['type'],
		'label'            => $data['label'],
		'input'            => $data['input'],
		'backend'          => '',
		'group'            => 'General Information',
		'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'          => true,
		'required'         => false,
		'user_defined'     => true,
		'visible_on_front' => true,
		'default'          => null
	));

	if (isset($data['wysiwyg']) && $data['wysiwyg']) {
		$installer->updateAttribute($entityTypeId, $attributeCode, 'is_wysiwyg_enabled', 1);
		$installer->updateAttribute($entityTypeId, $attributeCode, 'is_html_allowed_on_front', 1);
	}
}
$installer->endSetup();