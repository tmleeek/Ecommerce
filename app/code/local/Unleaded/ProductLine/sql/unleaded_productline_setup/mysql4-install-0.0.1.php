<?php

$installer = $this;

$installer->startSetup();

$installer->run("
 	DROP TABLE IF EXISTS {$this->getTable('unleaded_productline')};
	CREATE TABLE {$this->getTable('unleaded_productline')} (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(100) NOT NULL,
		`parent_category_id` int(11) NOT NULL,
		`product_line_short_code` varchar(100) NOT NULL,
		`i_sheet` varchar(100),
		`product_line_feature_benefits` TEXT,
		`product_line_install_video` varchar(100),
		`product_line_v01_video` varchar(100),
		`product_line_v02_video` varchar(100),
		`product_line_v03_video` varchar(100),
		`product_line_v04_video` varchar(100),
		`product_line_v05_video` varchar(100),
		`product_line_v06_video` varchar(100),
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

foreach ([
	'product_line_short_code', 'product_line_install_video', 'product_line_feature_benefits',
	'product_line_v01_video', 'product_line_v02_video', 'product_line_v03_video', 
	'product_line_v04_video', 'product_line_v05_video', 'product_line_v06_video'
] as $attributeCode) {
	$installer->removeAttribute('catalog_category', $attributeCode);
}

$installer->endSetup();