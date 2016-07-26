<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

# Create DB Structure
$installer = $this;
$installer->startSetup();

/*
 * [aw_vt_video_comment]-+
 * [aw_vt_video_store]---+
 *                       +---->[aw_vt_video]
 * [aw_vt_lock]
 */

$installer->run("
        
-- DROP TABLE IF EXISTS `{$this->getTable('aw_vt_video_comment')}`;
-- DROP TABLE IF EXISTS `{$this->getTable('aw_vt_video_store')}`;
-- DROP TABLE IF EXISTS `{$this->getTable('aw_vt_video')}`;
-- DROP TABLE IF EXISTS `{$this->getTable('aw_vt_lock')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_vt_video')}` (
  `video_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `author_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `api_code` varchar(100) NOT NULL,
  `api_video_id` varchar(255) NOT NULL,
  `api_video_url` varchar(255),
  `time` varchar(10),
  `thumbnail` varchar(255),
  `status` smallint,
  `state` smallint,
  `rate` tinyint(4) NOT NULL default '0',
  `votes` smallint(6) NOT NULL default '0',
  `read_only` tinyint  NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`video_id`),
  KEY `FK_VIDTEST_INT_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_VIDTEST_INT_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='aheadWorks Video Testimonials Entities';

CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_vt_video_comment')}` (
  `comment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `video_id` int(11) UNSIGNED NOT NULL,
  `comment` tinytext,
  PRIMARY KEY (`comment_id`, `video_id`),
  KEY `FK_VIDEO_COMMENT_INT_VIDEO_ID` (`video_id`),
  CONSTRAINT `FK_VIDEO_COMMENT_INT_VIDEO_ID` FOREIGN KEY (`video_id`) REFERENCES `{$this->getTable('aw_vt_video')}` (`video_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='aheadWorks Video Testimonials Entity Comments';

CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_vt_video_store')}` (
  `video_id` int(11) UNSIGNED NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`video_id`, `store_id`),
  KEY `FK_VIDEO_STORE_INT_VIDEO_ID` (`video_id`),
  CONSTRAINT `FK_STORE_RATE_INT_VIDEO_ID` FOREIGN KEY (`video_id`) REFERENCES `{$this->getTable('aw_vt_video')}` (`video_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY `FK_VIDEO_STORE_STORE` (`store_id`),
  CONSTRAINT `FK_VIDEO_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='aheadWorks Video Testimonials Entity Store';

CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_vt_lock')}` (
   `lock_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
   `product_id` int(10) UNSIGNED NOT NULL ,
   `check_at` datetime NOT NULL default '0000-00-00 00:00:00',
   `lock` tinyint(1) NOT NULL DEFAULT '0' ,
   PRIMARY KEY (`lock_id`),
   KEY `FK_VIDEO_LOCK_PRODUCT` (`product_id`),
   CONSTRAINT `FK_VIDEO_LOCK_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB CHARSET=utf8 COMMENT='aheadWorks Video Testimonials Product Lock';

");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

# Insert product attributes
$setup->addAttribute('catalog_product', 'vidtest_enabled', array(
        'backend_type'  => 'int',
        'is_global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'is_visible'    => '0',
        'required'      => false,
        'user_defined'  => false,
        'default'       => '0',
        'visible_on_front' => false
    ));
$setup->updateAttribute('catalog_product', 'vidtest_enabled', 'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE);
$setup->updateAttribute('catalog_product', 'vidtest_enabled', 'is_visible', 0);


$installer->endSetup();