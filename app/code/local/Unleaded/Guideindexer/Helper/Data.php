<?php

class Unleaded_Guideindexer_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getBrandGuides($brandId) {
        $brandGuidesCollection = Mage::getModel('guideindexer/productguides')->getCollection()->addFieldToFilter('brand', $brandId);

        $guides = [];

        foreach ($brandGuidesCollection as $_brandRow) {
            $iSheetsArr = explode(",", $_brandRow->getISheet());
            $guides = array_merge($guides,$iSheetsArr);
        }
        return $guides;
    }

    public function getCategoryGuides($brandId, $categoryId) {
        $categoryGuidesCollection = Mage::getModel('guideindexer/productguides')->getCollection()
                ->addFieldToFilter('brand', $brandId)
                ->addFieldToFilter('category', $categoryId);

        $guides = [];

        foreach ($categoryGuidesCollection as $_categoryRow) {
            $iSheetsArr = explode(",", $_categoryRow->getISheet());
            $guides = array_merge($guides,$iSheetsArr);
        }

        return $guides;
    }

}
