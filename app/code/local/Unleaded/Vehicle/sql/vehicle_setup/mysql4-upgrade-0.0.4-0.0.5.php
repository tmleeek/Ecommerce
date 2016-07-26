<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `description` TEXT NOT NULL AFTER `model` ;");

$installer->endSetup();
