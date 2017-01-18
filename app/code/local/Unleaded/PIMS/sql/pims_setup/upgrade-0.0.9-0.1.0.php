<?php

$installer = $this;
$installer->startSetup();

// Imports table
$sql = <<<SQLTEXT
CREATE TABLE `{$installer->getTable('unleaded_pims/import')}` (
	`entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`status` varchar(50) NOT NULL,
	`file` varchar(255) NOT NULL,
	`environment` varchar(50) NOT NULL,
	`imported` tinyint(1) NOT NULL DEFAULT 0,
	`rollback` varchar(255) NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  	`updated_at` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;
$installer->run($sql);

// Events Table
$sql = <<<SQLTEXT
CREATE TABLE `{$installer->getTable('unleaded_pims/event')}` (
	`entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`event_name` varchar(50) NOT NULL,
	`initiator` varchar(50) NOT NULL,
	`initiator_type` varchar(50) NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;
$installer->run($sql);

// Event For Table
$sql = <<<SQLTEXT
CREATE TABLE `{$installer->getTable('unleaded_pims/eventfor')}` (
	`entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`event_id` int(11) NOT NULL,
	`event_for_id` int(11) NOT NULL,
	`event_for_type` varchar(50) NOT NULL,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;
$installer->run($sql);

// Messages Table
$sql = <<<SQLTEXT
CREATE TABLE `{$installer->getTable('unleaded_pims/message')}` (
	`entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`type` varchar(50) NOT NULL,
	`initiator` varchar(50) NOT NULL,
	`initiator_type` varchar(50) NOT NULL,
	`body` mediumtext NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;
$installer->run($sql);

// Message For Table
$sql = <<<SQLTEXT
CREATE TABLE `{$installer->getTable('unleaded_pims/messagefor')}` (
	`entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`message_id` int(11) NOT NULL,
	`message_for_id` int(11) NOT NULL,
	`message_for_type` varchar(50) NOT NULL,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;
$installer->run($sql);

$installer->endSetup();