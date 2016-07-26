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



class Mirasvit_SeoAutolink_Model_Config_Source_Occurence
{
    const FIRST = 1;
    const LAST = 2;
    const RANDOM = 3;
    public function toOptionArray()
    {
        return array(
            array('value' => self::FIRST, 'label' => Mage::helper('seoautolink')->__('First')),
            array('value' => self::LAST, 'label' => Mage::helper('seoautolink')->__('Last')),
            array('value' => self::RANDOM, 'label' => Mage::helper('seoautolink')->__('Random')),
        );
    }
}
