<?php

class Unleaded_ProductLine_PartController extends Mage_Core_Controller_Front_Action 
{
    public function indexAction()
    {   
        $sku = $this->getRequest()->getParam('sku');

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        $return = [
            'compatibleVehicles' => Mage::helper('unleaded_productline')->getCompatibleVehicleListHtml($product),
            'price'              => Mage::helper('unleaded_productline')->getPriceHtml($product),
            'sku'                => $sku,
            'attributes'         => $this->_getPIMSAttributes($product)
        ];

        echo json_encode($return);
    }

    private function _getPIMSAttributes($product)
    {
        $valueAttributes = [
            'dim_a', 'dim_b', 'dim_c', 'dim_d', 'dim_e', 'dim_f', 'dim_g',
            'weight', 'interior_box_dimensions', 'light_power_rating', 'warranty', 'upc_code',
        ];
        $optionAttributes = [
            'length', 'height', 'width', 'liquid_storage_capacity', 'box_style', 'box_opening_type', 
            'material_thickness', 'material', 'style', 'country_of_manufacture', 'finish', 'tube_size', 'tube_shape', 
            'flare_tire_coverage', 'flare_height', 'sold_as', 'brand_short_code'
        ];
        $return = [];
        foreach ($valueAttributes as $attributeCode) {
            if ($this->_checkValue($product->getData($attributeCode))) {
                $return[$attributeCode] = $product->getData($attributeCode);
            }
        }
        foreach ($optionAttributes as $attributeCode) {
            if ($this->_checkValue($product->getData($attributeCode))) {
                $return[$attributeCode] = $product->getAttributeText($attributeCode);
            }
        }

        return $return;
    }

    private function _checkValue($value)
    {
        return $value && $value !== '' && $value !== '0.0' && $value != 0 && $value !== '0.00';
    }
}