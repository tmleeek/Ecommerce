<?php

$addToDefault = [
	'part_number',
	'part_saleable',
	'height',
	'width',
	'length',
	'vehicle_type',
	'model_type',
	'upc_code',
	'i_sheet',
	'part_lookup_number',
	'pop_code',
	'color',
	'meta_title',
	'meta_keyword',
	'brand_short_code',
	'finish',
	'style',
	'material',
	'material_thickness',
	'sold_as',
	'drilling_required',
	'oversize_shipping_required',
	'warranty',
	'short_description'
];

$attributeSets = [
	[
		'code'            => 'boxes',
		'label'           => 'Boxes',
		'attributesToAdd' => [
			'dim_a', 'dim_b', 'dim_c', 'dim_d', 'dim_e', 'dim_f', 'dim_g',
			'box_style', 'box_opening_type'
		]
	],
	[
		'code'            => 'bars',
		'label'           => 'Bars',
		'attributesToAdd' => [
			'tube_shape', 'tube_size', 
		]
	],
	[
		'code'            => 'liquid_storage',
		'label'           => 'Liquid Storage',
		'attributesToAdd' => [
			'liquid_storage_capacity'
		]
	],
	[
		'code'            => 'fender_flares',
		'label'           => 'Fender Flares',
		'attributesToAdd' => [
			'flare_height', 'flare_tire_coverage', 'bed_type', 'bed_length'
		]
	],
	[
		'code'            => 'combo_kits',
		'label'           => 'Combo Kits',
		'attributesToAdd' => []
	],
	[
		'code'            => 'trucks',
		'label'           => 'Trucks',
		'attributesToAdd' => [
			'bed_length'
		]
	]
];
