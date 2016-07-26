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


class AW_Vidtest_Block_Video extends Mage_Core_Block_Template {
    const DEFAULT_WIDTH = 640;
    const WIDTH_M = 16;
    const HEIGHT_M = 9;
    const MIN_SIZE = 200;

    public function __construct() {
        parent::__construct();
    }

    /*
     *  returns Youtube video id
     *  @return null | string
     */

    public function getVideoId() {

        $videoId = (int) $this->getData('testimonial_id');
        if ($videoId) {
            $video = Mage::getModel('vidtest/video')->load($videoId);

            if (
                    ($video->getStatus() == AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED)
                    && ($video->getState() == AW_Vidtest_Model_Video::VIDEO_STATE_READY)
            ) {
                return $video->getApiVideoId();
            }
        }
        return false;
    }

    public function getWidth() {
        $width = abs((int) $this->getData('width'));
        if ($width == 0) {
            $height = abs((int) $this->getData('height'));
            if ($height == 0) {
                $width = self::DEFAULT_WIDTH;
            } else {
                $width = ceil($this->getHeight() * self::WIDTH_M / self::HEIGHT_M);
            }
        }
        if ($width < self::MIN_SIZE) {
            $width = self::MIN_SIZE;
        }
        return $width;
    }

    public function getHeight() {
        $height = abs((int) $this->getData('height'));
        if ($height == 0) {
            $height = ceil($this->getWidth() * self::HEIGHT_M / self::WIDTH_M);
        }
        if ($height < self::MIN_SIZE) {
            $height = self::MIN_SIZE;
        }
        return $height;
    }

}