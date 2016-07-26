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
 * Show Source Input
 */
class AW_Vidtest_Model_Form_Element_Source extends Varien_Data_Form_Element_Text {

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml() {
        $html = "";
        $html .= "<div id=\"video_file_container\">
                        <input class=\"input-file\" id=\"video_file_field\" type=\"file\" name=\"video_file\" value=\"\" />
                  </div>";
        $html .= "<div id=\"video_link_container\">
                        <input class=\"input-text\" id=\"video_link_field\" type=\"text\" name=\"video_link\" value=\"\" />
                  </div>";
        return $html;
    }

}