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


class Mirasvit_Seo_Model_System_Config_Source_Metarobots extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function getAllOptions()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('seo')->__('Don\'t change')),
            array('value' => Mirasvit_Seo_Model_Config::NOINDEX_NOFOLLOW, 'label'=>Mage::helper('seo')->__('NOINDEX, NOFOLLOW')),
            array('value' => Mirasvit_Seo_Model_Config::NOINDEX_FOLLOW, 'label'=>Mage::helper('seo')->__('NOINDEX, FOLLOW')),
            array('value' => Mirasvit_Seo_Model_Config::INDEX_NOFOLLOW, 'label'=>Mage::helper('seo')->__('INDEX, NOFOLLOW')),
            array('value' => Mirasvit_Seo_Model_Config::INDEX_FOLLOW, 'label'=>Mage::helper('seo')->__('INDEX, FOLLOW')),
        );
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    public function getFlatColums()
    {
        return array();
    }

}
