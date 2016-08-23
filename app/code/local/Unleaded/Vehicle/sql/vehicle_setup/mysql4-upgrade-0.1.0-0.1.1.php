<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `description`");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `image`");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `trim`");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `type`");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `body_style`");
$installer->run("ALTER TABLE {$this->getTable('ul_vehicle_ymm')} DROP COLUMN `bed_length`");

$installer->endSetup();
