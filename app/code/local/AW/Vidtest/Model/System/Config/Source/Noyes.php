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

    /**
 * No/Yes Model (0 - Yes, 1 - No)
 */
class AW_Vidtest_Model_System_Config_Source_Noyes
{
    /**
     * Retrives array of options
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('Yes')),
            array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('No')),
        );
    }

}
