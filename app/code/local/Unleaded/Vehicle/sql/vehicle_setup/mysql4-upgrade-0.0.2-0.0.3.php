<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `image` VARCHAR( 255 ) NOT NULL AFTER `model` ");

$installer->endSetup();

