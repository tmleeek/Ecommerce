<?php

$installer = $this;

$installer->startSetup();

$installer->run(" ALTER TABLE {$this->getTable('ul_vehicle_garages')} ADD `selected_vehicle` INT NOT NULL AFTER `vehicles` ");

$installer->endSetup();
