<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP KEY UL_YEAR_MAKE_MODEL_UNIQUE;");

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD `sub_detail` VARCHAR(255) NOT NULL AFTER `sub_model`;");

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} ADD CONSTRAINT UL_YEAR_MAKE_MODEL_SUBMODEL_SUBDETAIL_UNIQUE UNIQUE (`year`,`make`,`model`,`sub_model`,`sub_detail`);");

$installer->endSetup();