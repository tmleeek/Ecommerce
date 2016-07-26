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
 * Videos Model Class
 */
class AW_Vidtest_Model_Video extends Mage_Core_Model_Abstract {

    /**
     * Model of Video Servise Api
     * @var AW_Vidtest_Model_Api_Abstract
     */
    protected $_apiModel = null;

    /**
     * Video State Unknown
     */
    const VIDEO_STATE_UNKNOWN = 0;

    /**
     * Video State Ready
     */
    const VIDEO_STATE_READY = 1;

    /**
     * Video State Processing
     */
    const VIDEO_STATE_PROCESSING = 2;

    /**
     * Video State Failed
     */
    const VIDEO_STATE_FAILED = 3;

    /**
     * Video State Rejected
     */
    const VIDEO_STATE_REJECTED = 4;

    /**
     * Video State Deleted
     */
    const VIDEO_STATE_DELETED = 5;

    /**
     * Status Enabled
     */
    const VIDEO_STATUS_ENABLED = 1;

    /**
     * Status Disabled
     */
    const VIDEO_STATUS_DISABLED = 0;

    /**
     * Status Pending
     */
    const VIDEO_STATUS_PENDING = 2;

    /**
     * Status Processing
     */
    const VIDEO_STATUS_PROCESSING = 3;

    /**
     * Class constructor
     */
    protected function _construct() {
        $this->_init('vidtest/video');
    }

    /**
     * Retrives statuses array
     * @return array
     */
    public function getStatusesArray() {
        $helper = Mage::helper('vidtest');
        return array(
            self::VIDEO_STATUS_ENABLED => $helper->__('Enabled'),
            self::VIDEO_STATUS_DISABLED => $helper->__('Disabled'),
            self::VIDEO_STATUS_PENDING => $helper->__('Pending'),
            self::VIDEO_STATUS_PROCESSING => $helper->__('Processing'),
        );
    }

    /**
     * Load connector of current video service
     * @return AW_Vidtest_Model_Video
     */
    public function loadApiModel() {
        if ($this->getApiCode()) {
            $this->_apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($this->getApiCode());
        }
        return $this;
    }

    /**
     * Retrives Video Service Connector
     * @return AW_Vidtest_Model_Connector
     */
    public function getApiModel() {
        if (!$this->_apiModel) {
            $this->loadApiModel();
        }
        return $this->_apiModel;
    }

    /**
     * Add store to stores of this model instanse
     * @param int|string|array|Mage_Core_Model_Store $storeId
     * @return AW_Vidtest_Model_Video
     */
    public function setStore($store) {
        if ($store && $store instanceof Mage_Core_Model_Store) {
            $this->setStore($store->getId());
        } elseif (is_array($store)) {
            foreach ($store as $id) {
                $this->setStore($id);
            }
        } elseif ($store) {
            if (!$this->getStores()) {
                $this->setStores(array($store));
            } else {
                $stores = $this->getStores();
                if (!in_array($store, $stores)) {
                    $stores[] = $store;
                    $this->setStores($stores);
                }
            }
        }
        return $this;
    }

    
    /**
     * Load stores to resource model
     * @param Mage_Core_Model_Abstract $object
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    public function loadStores() {
        $this->getResource()->loadStores($this);
        return $this;
    }

    /*
     *  @return string;
     */

    public function getComment() {
        $comment = $this->getData('comment');

        if ($comment === NULL && $this->getId()) {

            $comment = Mage::getModel('vidtest/video_comment')
                    ->loadByVideoId($this->getId());
            $comment = $comment->getData('comment');
            $this->setData('comment', (string) $comment);
        }
        return $comment;
    }

    
    /**
     * Retrives count of Views for current video
     * @return int
     */
    public function getViews() {
        if ($api = $this->getApiModel()) {
            return $api->getViews($this->getApiVideoId());
        }
        return 0;
    }

    /**
     * Retrives "Video Ready" flag
     * @return
     */
    public function getReady() {
        $apiModel = $this->getApiModel();
        $entry = $this->getApiModel()->getVideoData($this->getApiVideoId());
        if ($entry->getReady()) {
            $this->setApiVideoUrl($entry->getVideoUrl());
            $this->setThumbnail($entry->getThumbnailUrl());
            $this->setTime($entry->getTime());
            $this->setState(self::VIDEO_STATE_READY);
            $this->setStatus(self::VIDEO_STATUS_PENDING);
            $this->save();
            return true;
        } elseif ($entry->getRejected() || $entry->getFailed() || $entry->getDeleted()) {
            $this->setApiVideoUrl($entry->getVideoUrl());
            $this->setThumbnail($entry->getThumbnailUrl());
            $this->setTime($entry->getTime());
            $this->setStatus(self::VIDEO_STATUS_DISABLED);
            $state = self::VIDEO_STATE_UNKNOWN;
            if ($entry->getRejected()) {
                $state = self::VIDEO_STATE_REJECTED;
            } elseif ($entry->getFailed()) {
                $state = self::VIDEO_STATE_FAILED;
            } elseif ($entry->getDeleted()) {
                $state = self::VIDEO_STATE_DELETED;
            }
            $this->setState($state);
            $this->save();
            return false;
        }
        return false;
    }

   protected function _beforeDelete()
   {

        /* delete comments */
        $comments = Mage::getModel('vidtest/video_comment')
                ->getCollection()
                ->addFieldToFilter('video_id', array("eq" => $this->getId()))
        ;
        foreach ($comments as $comment) {
            $comment->delete();
        }
        parent::_beforeDelete();
    }

}
