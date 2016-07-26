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
 * Video Tesuomonials Video Processing Check Helper
 */
class AW_Vidtest_Helper_Processing extends Mage_Core_Helper_Abstract {
    /**
     * One time Videos count for check processing state at video service
     */
    const ONE_TIME_PROCESSING = 5;

    /**
     * Seconds for service don't check this product videos for wait
     */
    const SECONDS_LOCK = 10;

    /**
     * Seconds for unlock products if any error
     */
    const SECONDS_UNLOCK_IF_ERROR = 600;

    /**
     * Datetime Zend_Date format for time formatting
     */
    const DATETIME_FORMAT = 'YYYY-MM-dd HH:mm:ss';

    /**
     * Lock product for processing
     * @param int|string $product_id
     */
    protected function _lockProduct($product_id) {
        $lock = Mage::getModel('vidtest/lock')->load($product_id, 'product_id');
        if ($lock) {
            $lock->setProductId($product_id);
            $lock->setLock(1);
            $lock->save();
            $lock->getResource()->commit();
        }
    }

    /**
     * Unlock product after processing
     * @param int|string $product_id
     */
    protected function _unlockProductId($product_id) {
        $lock = Mage::getModel('vidtest/lock')->load($product_id, 'product_id');
        if ($lock) {
            $lock->setProductId($product_id);
            $lock->setLock(0);

            $date = new Zend_Date();
            $lock->setCheckAt($date->toString(self::DATETIME_FORMAT));
            $lock->save();
            $lock->getResource()->commit();
        }
    }

    /**
     * Compare two time and answer result compared with limit
     * 
     * @param Zend_Date $dbTime Time in DB
     * @param Zend_Date $currentTime Current time
     * @param int $limit Limit in seconds
     * @return boolean
     */
    protected function _compareTime($dbTime, $currentTime, $limit) {
        if ($dbTime && $currentTime) {
            $diff = ($currentTime->getTimestamp() - $dbTime->getTimestamp());
            return ($diff <= $limit);
        }
        return false;
    }

    /**
     * Retrives product lock state
     * @param int|string $product_id
     * @return boolean
     */
    protected function _checkLock($product_id) {
        $lock = Mage::getModel('vidtest/lock')->load($product_id, 'product_id');

        if ($lock->getLock() && !$this->_compareTime(new Zend_Date($lock->getCheckAt(), self::DATETIME_FORMAT), new Zend_Date(), self::SECONDS_UNLOCK_IF_ERROR)) {
            return true;
        } elseif (!$lock->getLock() && $lock->getCheckAt() && $this->_compareTime(new Zend_Date($lock->getCheckAt(), self::DATETIME_FORMAT), new Zend_Date(), self::SECONDS_LOCK)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check product videos for processing state
     * @param int|string $product_id
     * @return AW_Vidtest_Helper_Processing 
     */
    public function check($product_id) {
        if (!$this->_checkLock($product_id)) {
            $this->_lockProduct($product_id);

            $videos = Mage::getModel('vidtest/video')->getCollection()
                    ->addProductFilter($product_id)
                    ->addStatusFilter(AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED)
                    ->addStateFilter(AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING)
                    ->addStoreFilter(Mage::app()->getStore()->getId())
            ;

            $videos->getSelect()->limit(self::ONE_TIME_PROCESSING);
            foreach ($videos as $video) {
                $video->getReady();
            }

            $this->_unlockProductId($product_id);
        }
        return $this;
    }

}