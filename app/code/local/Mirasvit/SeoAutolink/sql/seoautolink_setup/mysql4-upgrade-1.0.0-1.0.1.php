<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.9
 * @build     1298
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



$installer = $this;
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('seoautolink');
if ($version == '1.0.1') {
    return;
} elseif ($version != '1.0.0') {
    die('Please, run migration 1.0.0');
}

$installer->startSetup();
$helper = Mage::helper('seoautolink/migration');

// 	drop index idx_seoautolink_keyword  on mage_m_seoautolink_link;
$sql = "create index idx_seoautolink_keyword on {$this->getTable('seoautolink/link')}(keyword);";

$helper->trySql($installer, $sql);
$installer->endSetup();
