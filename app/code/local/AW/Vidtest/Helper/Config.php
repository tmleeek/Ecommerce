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


class AW_Vidtest_Helper_Config extends Mage_Core_Helper_Abstract {
    //===========================Default constants================================

    /*
     * Page layout: Empty
     */
    const AW_VIDTEST_DEFAULT_FOR_EMPTY_PAGE_LAYOUT = 6;

    /*
     *  Page layout: 1 column
     */
    const AW_VIDTEST_DEFAULT_FOR_ONE_COLUMN_PAGE_LAYOUT = 5;

    /*
     * Page layout: 2 columns with left bar
     */
    const AW_VIDTEST_DEFAULT_FOR_TWO_COLUMNS_WLB_PAGE_LAYOUT = 4;

    /*
     * Page layout: 2 columns with left bar
     */
    const AW_VIDTEST_DEFAULT_FOR_TWO_COLUMNS_WRB_PAGE_LAYOUT = 4;

    /*
     * Page layout: 3 columns
     */
    const AW_VIDTEST_DEFAULT_FOR_THREE_COLUMNS_PAGE_LAYOUT = 2;

    //===========================Navigation arrows settings================================

    /*
     *  Page layout: Empty
     */
    const AW_VIDTEST_THUMBS_FOR_EMPTY_PAGE_LAYOUT = "vidtest/thumbs_for_layout/thumbs_for_layout_no_col";

    /*
     * Page layout: 1 column
     */
    const AW_VIDTEST_THUMBS_FOR_ONE_COLUMN_PAGE_LAYOUT = "vidtest/thumbs_for_layout/thumbs_for_layout_one_column";

    /*
     * Page layout: 2 columns with left bar
     */
    const AW_VIDTEST_THUMBS_FOR_TWO_COLUMNS_WLB_PAGE_LAYOUT = "vidtest/thumbs_for_layout/thumbs_for_layout_two_columns_wlb";

    /*
     * Page layout: 2 columns with right bar
     */
    const AW_VIDTEST_THUMBS_FOR_TWO_COLUMNS_WRB_PAGE_LAYOUT = "vidtest/thumbs_for_layout/thumbs_for_layout_two_columns_wrb";

    /*
     * Page layout: 3 columns
     */
    const AW_VIDTEST_THUMBS_FOR_THREE_COLUMNS_PAGE_LAYOUT = "vidtest/thumbs_for_layout/thumbs_for_layout_three_column";

//===========================Navigation arrows settings================================

    public function getThumbsForEmptyColLayout($storeId=null) {

        $result = $this->_getResult($storeId, self::AW_VIDTEST_DEFAULT_FOR_EMPTY_PAGE_LAYOUT, self::AW_VIDTEST_THUMBS_FOR_EMPTY_PAGE_LAYOUT);

        return $result;
    }

    public function getThumbsForOneColLayout($storeId=null) {

        $result = $this->_getResult($storeId, self::AW_VIDTEST_DEFAULT_FOR_ONE_COLUMN_PAGE_LAYOUT, self::AW_VIDTEST_THUMBS_FOR_ONE_COLUMN_PAGE_LAYOUT);

        return $result;
    }

    public function getThumbsForTwoColWlbLayout($storeId=null) {

        $result = $this->_getResult($storeId, self::AW_VIDTEST_DEFAULT_FOR_TWO_COLUMNS_WLB_PAGE_LAYOUT, self::AW_VIDTEST_THUMBS_FOR_TWO_COLUMNS_WLB_PAGE_LAYOUT);

        return $result;
    }

    public function getThumbsForTwoColWrbLayout($storeId=null) {

        $result = $this->_getResult($storeId, self::AW_VIDTEST_DEFAULT_FOR_TWO_COLUMNS_WRB_PAGE_LAYOUT, self::AW_VIDTEST_THUMBS_FOR_TWO_COLUMNS_WRB_PAGE_LAYOUT);

        return $result;
    }

    public function getThumbsForThreeColLayout($storeId=null) {

        $result = $this->_getResult($storeId, self::AW_VIDTEST_DEFAULT_FOR_THREE_COLUMNS_PAGE_LAYOUT, self::AW_VIDTEST_THUMBS_FOR_THREE_COLUMNS_PAGE_LAYOUT);

        return $result;
    }

    protected function _getResult($_storeId, $_defaultValue, $_configKey) {

        $toReturn = $_defaultValue;

        if ($_storeId) {

            $valueFromConfig = (int) Mage::getStoreConfig($_configKey, $_storeId);

            if ($valueFromConfig)
                $toReturn = $valueFromConfig;
        } else {

            $valueFromConfig = (int) Mage::getStoreConfig($_configKey);

            if ($valueFromConfig)
                $toReturn = $valueFromConfig;
        }

        return $toReturn;
    }

}

?>
