<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `sub_model` VARCHAR( 255 ) NOT NULL AFTER `type` ;");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `body_style` VARCHAR( 255 ) NOT NULL AFTER `type` ;");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `bed_length` VARCHAR( 255 ) NOT NULL AFTER `type` ;");

$installer->endSetup();
