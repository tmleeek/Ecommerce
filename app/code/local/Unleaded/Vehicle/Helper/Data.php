<?php

class Unleaded_Vehicle_Helper_Data extends Mage_Core_Helper_Abstract {

    public function mapAttributeOptionLabelToId($attrCode, $attrLabel) {
        $_product = Mage::getModel('catalog/product');
        $attr = $_product->getResource()->getAttribute($attrCode);
        if ($attr->usesSource()) {
            return $attr->getSource()->getOptionId($attrLabel);
        }
    }

    public function mapAttributeOptionIdToLabel($attrCode, $attrId) {
        $_product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getStoreId())
                ->{'set'.ucwords($attrCode)}($attrId);
        return $_product->getAttributeText($attrCode);
    }

}
