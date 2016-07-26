<?php

$this->startSetup()->run("
CREATE TABLE {$this->getTable('guideindexer')} (
   `guideindexer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `brand` int(11) DEFAULT NULL,
   `category` int(11) DEFAULT NULL,
   `product_line` int(11) DEFAULT NULL,
   `i_sheet` text,
   `flag` int(1) NOT NULL DEFAULT '0',
   `update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`guideindexer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
")->endSetup();
