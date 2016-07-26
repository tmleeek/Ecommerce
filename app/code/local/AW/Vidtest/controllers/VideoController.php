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
 * Video Controller
 */
class AW_Vidtest_VideoController extends Mage_Core_Controller_Front_Action {

    /**
     * Response for Ajax Request
     * @param array $result
     */
    protected function _ajaxResponse($result = array()) {
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Rate action
     */
    public function rateAction() {
        if (($id = $this->getRequest()->getParam('id')) && ($rate = $this->getRequest()->getParam('rate'))) {
            if (!(($rate >= 1) && ($rate <= 5))) {
                $this->_ajaxResponse(array('error' => 'Wrong rate param'));
                return;
            }

            if (Mage::helper('vidtest')->confRatingStatus() < AW_Vidtest_Model_System_Config_Source_Rating::STATUS_DISPLAY_AND_RATE) {
                $this->_ajaxResponse(array('error' => 'Rating disallowed'));
                return;
            }

            $video = Mage::getModel('vidtest/video')->load($id);
            if ($video->getVideoId()) {

                if (Mage::helper('vidtest')->isRateRegistered($id)) {
                    $this->_ajaxResponse(array('error' => 'You already rate this video'));
                    return;
                }

                $votes = ($video->getVotes() >= 0) ? $video->getVotes() : 0;
                $oldRate = $video->getRate();

                $sum = $votes * $oldRate + ($rate * 20);
                $votes++;
                $rate = round($sum / $votes);

                $rate = min(100, $rate);
                $rate = max(0, $rate);


                $video->setVotes($votes);
                $video->setRate($rate);
                $video->save();
                Mage::helper('vidtest')->registerRate($id);
                $this->_ajaxResponse(array('newrate' => $rate));
                return;
            }
            $this->_ajaxResponse(array('error' => 'Video not found'));
        } else {
            $this->_ajaxResponse(array('error' => 'Video id missed'));
        }
    }

}