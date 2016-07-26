<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_garages')} CHANGE `customer_id` `customer_id` VARCHAR( 255 ) NOT NULL");

$installer->endSetup();