<?php
class Unleaded_YMM_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{

    /**
     * Initialize product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    {

        //Check if we have a vehicle
        if($currentVehicle = Mage::getSingleton('core/cookie')->get('currentVehicle')):

        	$vehicle = Mage::helper('unleaded_ymm')->getVehicleFromSegment($currentVehicle);
        	$vehicleId = Mage::helper('unleaded_ymm')->getVehicleIdsFromSegment($vehicle);

	        $collection
	            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
	            ->addAttributeToFilter('compatible_vehicles', array('finset' => $vehicleId[0]))
	            ->addMinimalPrice()
	            ->addFinalPrice()
	            ->addTaxPercents()
	            ->addUrlRewrite($this->getCurrentCategory()->getId());

        else:

	        $collection
	            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
	            ->addMinimalPrice()
	            ->addFinalPrice()
	            ->addTaxPercents()
	            ->addUrlRewrite($this->getCurrentCategory()->getId());

        endif;

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        return $this;
    }

}