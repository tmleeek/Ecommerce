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
 * Video Resource Model
 */
class AW_Vidtest_Model_Mysql4_Video extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_init('vidtest/video', 'video_id');
    }

    /**
     * Processing object before save data
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        $date = new Zend_Date();
        $object->setUpdatedAt($date->toString('YYYY-MM-dd HH:mm'));
        return $this;
    }

    /**
     * Processing object after save data
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);
        $this->_saveStores($object);
        $this->_saveComment($object);
        return $this;
    }


    /**
     * Load stores to model
     * @param Mage_Core_Model_Abstract $object
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    public function loadStores(Mage_Core_Model_Abstract $object)
    {
        $this->_loadStores($object);
        return $this;
    }

    /**
     * Load stores to model
     * @param Mage_Core_Model_Abstract $object
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    protected function _loadStores(Mage_Core_Model_Abstract $object)
    {
        $video_id = $object->getVideoId();
        if($video_id){
            $videoStore = $this->getTable('vidtest/store');
            $select = new Zend_Db_Select($this->_getReadAdapter());
            $select->from($videoStore, array('store_id'))
                    ->where('video_id = ?', $video_id)
                    ;
            $stores = array();
            foreach ($select->query() as $store){
                $stores[] = $store['store_id'];
            }
            $object->setStores($stores);
        }
        return $this;
    }


    /**
     * Save comment to db
     * @param Mage_Core_Model_Abstract $object
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    protected function _saveComment(Mage_Core_Model_Abstract $object)
    {
        $video_id = $object->getVideoId();
        $comment = Mage::getModel('vidtest/video_comment')
                ->loadByVideoId($video_id);

        $commentField = $comment->getComment();
        $commentText = isset($commentField) == true ? $commentField: $object->getData('comment');

        $comment
                ->setVideoId($video_id)
                ->setComment($commentText)
                ->save();
        return $this;
    }

    /**
     * Save stores to db
     * @param Mage_Core_Model_Abstract $object
     * @return AW_Vidtest_Model_Mysql4_Video
     */
    protected function _saveStores(Mage_Core_Model_Abstract $object)
    {
        $video_id = $object->getVideoId();
        $videoStore = $this->getTable('vidtest/store');

        $this->_getWriteAdapter()->delete(
            $videoStore,
            $this->_getWriteAdapter()->quoteInto('video_id=?', $video_id)
        );
        
        $stores = $object->getStores();
        if (count($stores)){
            foreach ($stores as $store_id){
                $row = array(
                    'video_id' => $video_id,
                    'store_id' => $store_id
                );

                $fields = array();
                $values = array();
                foreach ($row as $k => $v) {
                    $fields[] = $this->_getWriteAdapter()->quoteIdentifier('?', $k);
                    $values[] = $this->_getWriteAdapter()->quoteInto('?', $v);
                }
                $sql = sprintf('INSERT IGNORE INTO %s (%s) VALUES(%s)',
                    $this->_getWriteAdapter()->quoteIdentifier($videoStore),
                    join(',', array_keys($row)),
                    join(',', $values));
                $this->_getWriteAdapter()->query($sql);
            }
        }

        return $this;
    }
    
    /**
     * Processing object after load data
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        $this->_loadStores($object);
        return $this;
    }
}