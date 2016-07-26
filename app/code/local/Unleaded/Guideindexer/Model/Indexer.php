<?php

class Unleaded_Guideindexer_Model_Indexer extends Mage_Index_Model_Indexer_Abstract {

    protected $_matchedEntities = array(
        'guideindexer_entity' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );
    // var to protect multiple runs
    protected $_registered = false;
    protected $_processed = false;
    protected $_categoryId = 0;
    protected $_productLineIds = array();
    protected $_productLineId = '';

    /**
     * not sure why this is required.
     * _registerEvent is only called if this function is included.
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event) {
        return Mage::getModel('catalog/category_indexer_product')->matchEvent($event);
    }

    public function getName() {
        return Mage::helper('guideindexer')->__('Category Product Guide');
    }

    public function getDescription() {
        return Mage::helper('guideindexer')->__('Refresh The Product Guides Per Category.');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event) {
        // if event was already registered once, then no need to register again.
        if ($this->_registered)
            return $this;

        $entity = $event->getEntity();
        switch ($entity) {
            case Mage_Catalog_Model_Product::ENTITY:
                $this->_registerProductEvent($event);
                break;

            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerCategoryEvent($event);
                break;

            case Mage_Catalog_Model_Convert_Adapter_Product::ENTITY:
                $event->addNewData('guideindexer_indexer_reindex_all', true);
                break;

            case Mage_Core_Model_Store::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
        $this->_registered = true;
        return $this;
    }

    /**
     * Register event data during product save process
     *
     * @param Mage_Index_Model_Event $event
     * Call Squence 3
     */
    protected function _registerProductEvent(Mage_Index_Model_Event $event) {
        $eventType = $event->getType();

        if ($eventType == Mage_Index_Model_Event::TYPE_MASS_ACTION) {
            $process = $event->getProcess();
            $productIds = $event->getDataObject()->getData('product_ids');

            foreach ($productIds as $productId) {
                $categoryIds = Mage::getModel('catalog/product')->load($productId)->getCategoryIds();
                $this->_productLineIds[] = end($categoryIds);
            }
            $this->flagIndexRequired($this->_productLineIds, 'product_line');
        } elseif ($eventType == Mage_Index_Model_Event::TYPE_SAVE) {
            $process = $event->getProcess();

            $productId = $event->getDataObject()->getData('entity_id');
            $categoryIds = Mage::getModel('catalog/product')->load($productId)->getCategoryIds();
            $this->_productLineId = end($categoryIds);

            $this->flagIndexRequired($this->_productId, 'product_line');
        }
        $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
    }

    /**
     * Register event data during category save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerCategoryEvent(Mage_Index_Model_Event $event) {
        $category = $event->getDataObject();
        /**
         * Check if product categories data was changed
         * Check if category has another affected category ids (category move result)
         */
        if ($category->getIsChangedProductList() || $category->getAffectedCategoryIds()) {
            $process = $event->getProcess();
            $this->_categoryId = $event->getDataObject()->getData('entity_id');
            $this->flagIndexRequired($this->_categoryId, 'category_id');

            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }

    protected function _processEvent(Mage_Index_Model_Event $event) {
        if (!$this->_processed) {
            $this->_processed = true;
        }
    }

    public function flagIndexRequired($ids, $type = 'product_line') {
        $collection = Mage::getModel('guideindexer/productguides')->getCollection();

        if ($type == 'product_line') {
            $filter = array();
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $filter[] = array('eq' => $id);
                }
            } else {
                $filter[] = array('eq' => $ids);
            }
            $collection->addFieldToFilter($type, $filter);
            $collection->setDataToAll('flag', 1);
            $collection->save();
        } elseif ($type == 'category_id') {
            $fields = array('brand', 'category', 'product_line');
            $filter = array(
                array('eq' => $ids),
                array('eq' => $ids),
                array('eq' => $ids)
            );
            $collection->addFieldToFilter($fields, $filter);
            $collection->setDataToAll('flag', 1);
            $collection->save();
        }
    }

    public function reindexAll() {
        $refreshableIndexes = Mage::getModel('guideindexer/productguides')->getCollection()->addFieldToFilter('flag', 1);
        if ($refreshableIndexes->getSize() > 0) {
            foreach ($refreshableIndexes as $ri) {
                try {
                    Mage::getModel('guideindexer/productguides')->refreshProductGuidesIndex($ri->getData('product_line'));
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                    return;
                }
            }
        } else {
            $brandCategories = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('name', ['in' => ['AVS', 'Lund']])
                    ->addAttributeToFilter('level', '3');
            $data = [];
            foreach ($brandCategories as $brandCategory) {
                if (!isset($data[$brandCategory->getId()]))
                    $data[$brandCategory->getId()] = [];

                foreach ($brandCategory->getChildrenCategories() as $childCategory) {
                    if (!isset($data[$brandCategory->getId()][$childCategory->getId()]))
                        $data[$brandCategory->getId()][$childCategory->getId()] = [];

                    foreach ($childCategory->getChildrenCategories() as $productLineCategory) {
                        if (!isset($data[$brandCategory->getId()][$childCategory->getId()][$productLineCategory->getId()]))
                            $data[$brandCategory->getId()][$childCategory->getId()][$productLineCategory->getId()] = [];

                        foreach ($productLineCategory->getProductCollection()->addAttributeToSelect('i_sheet') as $product) {
                            if (in_array($product->getAttributeText('i_sheet'), $data[$brandCategory->getId()][$childCategory->getId()][$productLineCategory->getId()]) || $product->getAttributeText('i_sheet') == 'NONE' || $product->getAttributeText('i_sheet') == '')
                                continue;

                            $data[$brandCategory->getId()][$childCategory->getId()][$productLineCategory->getId()][] = $product->getAttributeText('i_sheet');
                        }
                    }
                }
            }

            $rows = [];
            foreach ($data as $brandId => $categories) {
                foreach ($categories as $categoryId => $productLines) {
                    foreach ($productLines as $productLineId => $iSheet) {
                        if (empty($iSheet))
                            continue;

                        $rows[] = [
                            'brand' => $brandId,
                            'category' => $categoryId,
                            'product_line' => $productLineId,
                            'i_sheet' => implode(",", $iSheet),
                        ];
                    }
                }
            }

            foreach ($rows as $row) {
                try {
                    $guideModel = Mage::getModel('guideindexer/productguides');
                    $guideModel->setData($row);
                    $guideModel->save();
                } catch (Exception $ex) {
                    echo "Error: " . $ex->getMessage();
                }
            }
        }
    }
}
