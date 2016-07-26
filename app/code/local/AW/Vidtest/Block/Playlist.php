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


class AW_Vidtest_Block_Playlist extends AW_Vidtest_Block_Video {

    protected $_videoCollection = null;

    public function __construct() {
        parent::__construct();
    }

    public function getVideoId() {

        $video = $this->getVideoCollection()->getFirstItem();

        return $video->getData('api_video_id');
    }

    /*
     *  @array   Youtube api_video_ids
     */

    public function getAllVideoIds() {

        $ids = $this->getVideoCollection()->getColumnValues('api_video_id');

        return $ids;
    }

    public function getVideoCollection() {

        if (is_null($this->_videoCollection)) {

            $this->_videoCollection = Mage::getModel('vidtest/video')->getCollection()
                    ->addStatusFilter(AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED)
                    ->addStateFilter(AW_Vidtest_Model_Video::VIDEO_STATE_READY)
                    ->addStoreFilter(Mage::app()->getStore()->getId());

            $productId = $this->getProductId();
            if ($productId) {
                $this->_videoCollection->addProductFilter($productId);
            }
        }
        return $this->_videoCollection;
    }

    /*
     *  @return bool|int
     */

    public function getProductId() {

        if (strtolower($this->getData('product_id')) == 'all') {
            return false;
        }
        
        $productId = (int) $this->getData('product_id');
        $currentProduct = Mage::registry('current_product');

        if ((!$productId) && ($currentProduct)) {
            $productId = $currentProduct->getId();
        }

        return $productId;
    }

}