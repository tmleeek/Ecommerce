<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD CONSTRAINT UL_YEAR_MAKE_MODEL_UNIQUE UNIQUE (`year`,`make`,`model`);");

$installer->endSetup();