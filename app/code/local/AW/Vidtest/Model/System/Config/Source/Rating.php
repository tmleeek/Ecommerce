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
 * Retrives Rating Option
 */
class AW_Vidtest_Model_System_Config_Source_Rating
{
    /**
     * Display and allow rating status
     */
    const STATUS_DISPLAY_AND_RATE = 2;

    /**
     * Display rating status
     */
    const STATUS_DISPLAY = 1;

    /**
     * Disable rating status
     */
    const STATUS_DISABLED = 0;

    /**
     * Array with params for Select element
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::STATUS_DISPLAY_AND_RATE, 'label' => 'Display and allow rating'),
            array('value' => self::STATUS_DISPLAY, 'label' => 'Display'),
            array('value' => self::STATUS_DISABLED, 'label' => 'Disabled'),
        );
    }
}