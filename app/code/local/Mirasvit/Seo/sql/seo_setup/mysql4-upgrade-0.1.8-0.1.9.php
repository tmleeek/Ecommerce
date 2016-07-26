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
$version = Mage::helper('mstcore/version')->getModuleVersionFromDb('seo');
if ($version == '0.1.9') {
    return;
} elseif ($version != '0.1.8') {
    die("Please, run migration 0.1.8");
}

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

//PRODUCT
$setup->addAttribute('catalog_product', 'seo_canonical_store_id', array(
    'group'         => 'Meta Information',
    'input'         => 'select',
    'type'          => 'int',
    'source'        => 'seo/system_config_source_canonical',
    'label'         => 'Main Store for Canonical Url',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'sort_order'    => 100000,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));


$installer->endSetup();
