<?php

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('ul_vehicle_garages')};
CREATE TABLE {$this->getTable('ul_vehicle_garages')} (
 `garage_id` int(11) NOT NULL AUTO_INCREMENT,
 `customer_id` int(11) NOT NULL,
 `vehicles` varchar(255) NOT NULL,
 PRIMARY KEY (`garage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('ul_vehicle_ymm')};
CREATE TABLE {$this->getTable('ul_vehicle_ymm')} (
 `ymm_id` int(11) NOT NULL AUTO_INCREMENT,
 `year` int(11) NOT NULL,
 `make` varchar(100) NOT NULL,
 `model` varchar(100) NOT NULL,
 `trim` varchar(100) NOT NULL,
 `type` varchar(100) NOT NULL,
 PRIMARY KEY (`ymm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
