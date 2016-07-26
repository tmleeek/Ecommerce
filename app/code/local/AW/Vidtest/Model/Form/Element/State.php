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
 * Video state renderer for backend area
 */
class AW_Vidtest_Model_Form_Element_State extends Varien_Data_Form_Element_Text {

    /**
     * Basical states ofvideoservices
     * @var array
     */
    protected $_states = array(
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_UNKNOWN, 'label' => 'Unknown', 'color' => 'black'),
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING, 'label' => 'Processing', 'color' => 'blue'),
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_READY, 'label' => 'Ready', 'color' => 'green'),
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_REJECTED, 'label' => 'Rejected', 'color' => 'red'),
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_FAILED, 'label' => 'Failed', 'color' => 'red'),
        array('value' => AW_Vidtest_Model_Video::VIDEO_STATE_DELETED, 'label' => 'Deleted', 'color' => 'red'),
    );

    /**
     * Retrives translated and colored label
     * @param string $label Label
     * @param string $color Color of label
     * @return string
     */
    protected function _drawState($label, $color) {
        $label = Mage::helper('vidtest')->__($label);
        return "<strong style=\"color: {$color};\">{$label}</strong>";
    }

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml() {
        $value = $this->getValue() ? $this->getValue() : 0;
        foreach ($this->_states as $state) {
            if ($state['value'] == $value) {
                return $this->_drawState($state['label'], $state['color']);
            }
        }
    }

}