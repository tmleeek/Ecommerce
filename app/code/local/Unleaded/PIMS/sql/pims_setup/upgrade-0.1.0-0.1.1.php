<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE {$installer->getTable('unleaded_pims/import')} ADD `store_code` varchar(20) NOT NULL;");
$installer->run("ALTER TABLE {$installer->getTable('unleaded_pims/import')} ADD `import_type` varchar(20) NOT NULL;");
$installer->run("ALTER TABLE {$installer->getTable('unleaded_pims/import')} ADD `operation` varchar(20) NOT NULL;");

$installer->endSetup();