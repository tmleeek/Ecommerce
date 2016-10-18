<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('unleaded_productline')} ADD `product_tree` LONGTEXT;");

$installer->endSetup();