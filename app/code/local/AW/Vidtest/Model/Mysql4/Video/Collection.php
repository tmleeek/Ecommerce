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
 * Video Collection
 */
class AW_Vidtest_Model_Mysql4_Video_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Video Entity Table Name
     * @var string
     */
    protected $_videoTable;

    /**
     * Video Entity Comment Table Name
     * @var string
     */
    protected $_videoCommentTable;

    /**
     * Video Entity Rate Table Name
     * @var string
     */
    protected $_videoRateTable;

    /**
     * Video Entity Store Table Name
     * @var string
     */
    protected $_videoStoreTable;

    /**
     * Public Date Format Cache
     * @var string
     */
    protected $_publicDateFormat;

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('vidtest/video');

        $resources = Mage::getSingleton('core/resource');

        $this->_videoTable = $resources->getTableName('vidtest/video');
        $this->_videoCommentTable = $resources->getTableName('vidtest/comment');
        $this->_videoStoreTable = $resources->getTableName('vidtest/store');
    }

    /**
     * Add field filter to collection
     *
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     *
     * @see self::_getConditionSql for $condition
     * @param string|array $attribute
     * @param null|string|array $condition
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilter($field, $condition=null)
    {
        if ($field == 'product_name'){

            $this->_select->where('_product_name_value.value LIKE ?', $condition['like']);
            return $this;
        }
        parent::addFieldToFilter('main_table.'.$field, $condition);
        return $this;
    }

    /**
     * Add store filter
     * @param Mage_Core_Model_Store|array|string|int $store
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function addStoreFilter($store)
    {
        if ($store){
            if ($store instanceof Mage_Core_Model_Store){
                $storeIds = $store->getId();
            } elseif(is_numeric($store) || is_string($srore)) {
                $storeIds = $store;
            } elseif(is_array($srore)) {
                $storeIds = implode(",", $store);
            } else {
                return $this;
            }
        } else {
            return $this;
        }
         $this->getSelect()
             ->join(array('video_store'=>$this->_videoStoreTable), "video_store.video_id = main_table.video_id AND video_store.store_id IN ({$storeIds})", array())
        ;
        return $this;
    }

    /**
     * Add product filter
     * @param Mage_Catalog_ModelProduct|int $product
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function addProductFilter($product)
    {
        if ($product instanceof Mage_Core_Model_Product){
            $product_id = $product->getId();
        } elseif(is_numeric($product)) {
            $product_id = $product;
        } else {
            return $this;
        }
        $this->getSelect()
             ->where('main_table.product_id = ?', $product_id)
        ;
        return $this;
    }

    /**
     * Join External Data to Collection
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    protected function _joinExtData()
    {
        $this->getSelect()
             # Join internal data
             ->joinLeft(array('video_comment'=>$this->_videoCommentTable), "video_comment.video_id = main_table.video_id", array('comment'=>'comment'))
             ;
        return $this;
    }

    /**
     * Join Product Name to collection
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public  function joinProductNames()
    {
        $storeId = Mage::app()->getStore()->getId();
        # eav_attribute
        $attribute = $this->getTable('eav/attribute');
        # eav_entity_type
        $entity_type = $this->getTable('eav/entity_type');
        # catalog_product_entity_varchar
        $productVarchar = $this->getTable('catalog/product')."_varchar";

        $this->getSelect()
             ->joinLeft(array('_product_name_eavType'=>$entity_type),
                                "_product_name_eavType.entity_type_code = 'catalog_product'",
                                array())

             ->joinLeft(array('_product_name_attribute'=>$attribute),
                                "_product_name_attribute.entity_type_id = _product_name_eavType.entity_type_id
                                AND _product_name_attribute.attribute_code = 'name'",
                                array())

             ->joinLeft(array('_product_name_value'=>$productVarchar),
                                "_product_name_value.attribute_id = _product_name_attribute.attribute_id
                                AND _product_name_value.entity_id = main_table.product_id
                                AND _product_name_value.store_id = {$storeId}",
                                array('product_name' => 'value'))
             ;
                                
        return $this;
    }

    /**
     * Add status filter
     * + AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED
     * + AW_Vidtest_Model_Video::VIDEO_STATUS_DISABLED
     * + AW_Vidtest_Model_Video::VIDEO_STATUS_PENDING
     *
     * @param   int|string $status
     * @return  AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()
             ->where('main_table.status = ?', $status)
        ;
        return $this;
    }

    /**
     * Add state filter
     * + AW_Vidtest_Model_Video::VIDEO_STATE_UNKNOWN
     * + AW_Vidtest_Model_Video::VIDEO_STATE_READY
     * + AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING
     * + AW_Vidtest_Model_Video::VIDEO_STATE_FAILED
     * + AW_Vidtest_Model_Video::VIDEO_STATE_REJECTED
     * + AW_Vidtest_Model_Video::VIDEO_STATE_DELETED
     *
     * @param   int|string $state
     * @return  AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function addStateFilter($state)
    {
        $this->getSelect()
              ->where('main_table.state = ?', $state);
        return $this;
    }

    public function load($printQuery=false, $logQuery=false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_isFirstLoaded = true;
        if (!$this->_isFirstLoaded){
            $this->_joinExtData();
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }

    /**
     * Set up ordering by rate
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function setOrderByRate()
    {
        $this->getSelect()->order('main_table.rate DESC');
        return $this;
    }
    
    /**
     * Set up ordering by rand
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function setOrderByRand()
    {
        $this->getSelect()->order(new Zend_Db_Expr('RAND()'));
        return $this;
    }
    
    /**
     * Redeclare after load method for specifying collection items original data
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $needLoadAgain = false;
        foreach ($this->_items as $item) {
            $item->loadStores();
            $this->_addPublicDate($item);
        }
        return $this;
    }


    /**
     * Add public date to item object
     * @param Varien_Object $item
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    protected function _addPublicDate($item)
    {
        if ($createdAt = $item->getCreatedAt()){
            $date = Mage::getModel('core/locale')->date();
            $item->setPublicDate($date->toString($this->getDateFormat()));            
        }
        return $this;
    }

    /**
     * Retrives date format
     * @return string
     */
    public function getDateFormat()
    {
        if (!$this->_publicDateFormat){
            $this->_publicDateFormat = Mage::app()->getLocale()->getDateFormat( Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM );
        }
        return $this->_publicDateFormat;
    }
}
