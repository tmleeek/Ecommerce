<?php

class Unleaded_Guideindexer_Model_Productguides extends Mage_Core_Model_Abstract {

    /**
     * Initialize resource
     */
    protected function _construct() {
        $this->_init('guideindexer/productguides');
    }

    protected function _beforeSave() {
        parent::_beforeSave();
    }

    protected function _afterSave() {
        parent::_afterSave();
    }

    /**
     * @param int $productLineId
     */
    public function refreshProductGuidesIndex($productLineId) {
        $indexerItem = Mage::getModel('guideindexer/productguides')->getCollection()->AddFieldToFilter('product_line', $productLineId)->getFirstItem();
        $productLine = Mage::getModel('catalog/category')->load($productLineId);

        $data = explode(",", $indexerItem->getISheet());
        foreach ($productLine->getProductCollection()->addAttributeToSelect('i_sheet') as $product) {
            if (in_array($product->getAttributeText('i_sheet'), $data) || $product->getAttributeText('i_sheet') == 'NONE' || $product->getAttributeText('i_sheet') == '')
                continue;

            $data[] = $product->getAttributeText('i_sheet');
        }

        if ($indexerItem->getISheet() != implode(",", $data)) {
            try {
                $indexerItem->setISheet(implode(",", $data));
                $indexerItem->setFlag(0);
                $indexerItem->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        } else {
            try {
                $indexerItem->setFlag(0);
                $indexerItem->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        }
        return;
    }

}
