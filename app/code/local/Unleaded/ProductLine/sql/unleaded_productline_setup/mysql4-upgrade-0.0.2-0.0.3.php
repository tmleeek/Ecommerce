<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('unleaded_productline')} DROP COLUMN `i_sheet`;");

$installer->endSetup();