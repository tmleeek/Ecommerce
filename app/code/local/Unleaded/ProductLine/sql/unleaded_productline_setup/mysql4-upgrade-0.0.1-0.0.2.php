<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('unleaded_productline')} ADD `description` TEXT;");
$installer->run("ALTER TABLE {$this->getTable('unleaded_productline')} ADD `short_description` varchar(255);");

$installer->endSetup();