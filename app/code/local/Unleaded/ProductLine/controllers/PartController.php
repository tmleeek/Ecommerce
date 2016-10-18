<?php

class Unleaded_ProductLine_PartController extends Mage_Core_Controller_Front_Action 
{
    public function indexAction()
    {   
        $sku = $this->getRequest()->getParam('sku');

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        $return = [
            'compatibleVehicles' => Mage::helper('unleaded_productline')->getCompatibleVehicleListHtml($product),
            'price' => Mage::helper('unleaded_productline')->getPriceHtml($product)
        ];

        echo json_encode($return);
    }
}