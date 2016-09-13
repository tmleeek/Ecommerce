<?php

class Unleaded_Vehicle_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List {

    public function setCollection($collection) {
        $this->_productCollection = $collection;

        if ($this->getRequest()->getParam('brand')) {
            $brand = strtoupper($this->getRequest()->getParam('brand'));
            $productModel = Mage::getModel('catalog/product');
            $brandShortCode = $productModel->getResource()->getAttribute("brand_short_code");
            if ($brandShortCode->usesSource()) {
                $brandShortCodeId = $brandShortCode->getSource()->getOptionId($brand);
                $collection->addAttributeToFilter('brand_short_code', $brandShortCodeId);
            }
        }
        
        return $this;
    }

}
