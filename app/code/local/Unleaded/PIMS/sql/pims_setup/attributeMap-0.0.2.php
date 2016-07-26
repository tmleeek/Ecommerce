<?php

$attributesToAdd = [
	[
		'code'                          => 'part_saleable',
		'label'                         => 'Part Saleable',
		'input'                         => 'boolean',
		'is_searchable'                 => '0',
		'is_visible_in_advanced_search' => '0',
		'is_comparable'                 => '0',
		'is_visible_on_front'           => '0',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '0',
		'is_filterable'                 => '0',
		'is_filterable_in_search' 	    => '0',
	]
];

$attributeCodes = [
	'p01_off_vehicle'   => 'P01 - Off Vehicle',
	'p03_lifestyle'     => 'P03 - Lifestyle',
	'p04_primary_photo' => 'P04 - Primary Photo', 
	'p05_closeup'       => 'P05 - Closeup',
	'p06_mounted'       => 'P06 - Mounted',
	'p07_unmounted'     => 'P07 - Unmounted',
];
foreach ($attributeCodes as $code => $label) {
	$attributesToAdd[] = [
		'code'                          => $code,
		'label'                         => $label,
		'input'                         => 'media_image',
		'is_searchable'                 => '0',
		'is_visible_in_advanced_search' => '0',
		'is_comparable'                 => '0',
		'is_visible_on_front'           => '1',
		'used_in_product_listing'       => '1',
		'used_for_sort_by'              => '0',
		'is_filterable'                 => '0',
		'is_filterable_in_search' 	    => '0',
	];
}