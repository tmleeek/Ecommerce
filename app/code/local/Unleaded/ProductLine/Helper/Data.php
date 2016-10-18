<?php

class Unleaded_ProductLine_Helper_Data extends Mage_Core_Helper_Abstract 
{
    public function getCompatibleVehicleListHtml($product)
    {
    	Mage::register('current_product', $product);
    	$layout = Mage::getSingleton('core/layout');
		$html = $layout
	            ->createBlock('core/template')
	            ->setData('wrapper', false)
	            ->setTemplate('ulvehicle/catalog/product/view/compatible-vehicles.phtml')
	            ->toHtml();
	    return $html;
    }

    public function getPriceHtml($product)
    {
    	preg_match('/^([0-9]*)\.([0-9]{2})/', $product->getPrice(), $matches);
    	return '<sup>$</sup>' . $matches[1] . '<sup class="decimals">.' 
    		. $matches[2] . '</sup>';
    }
}