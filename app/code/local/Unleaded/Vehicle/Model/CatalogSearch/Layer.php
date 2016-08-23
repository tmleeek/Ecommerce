<?php

class Unleaded_Vehicle_Model_CatalogSearch_Layer extends Mage_CatalogSearch_Model_Layer {

    public function prepareProductCollection($collection) {

        if (Mage::helper('catalogsearch')->getQuery()->getQueryText())//for normal search we get the value from query string q=searchtext
            return parent::prepareProductCollection($collection);
        else {

            $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
            /**
             * make sure you cross check the $_REQUEST with $attributes
             */
            $attributes = Mage::getSingleton('catalog/product')->getAttributes();

            $request = Mage::app()->getRequest();

            Mage::log(print_r($request->getParams(), 1));

            foreach ($attributes as $attribute) {
                $attribute_code = $attribute->getAttributeCode();
                //Mage::log("--->>". $attribute_code);
                if ($attribute_code == "price")//since i am not using price attribute
                    continue;

                if (!$attribute_value = $request->getParam($attribute_code)) {
                    //Mage::log("nothing found--> $attribute_code");
                    continue;
                }

                if (is_array($attribute_value)) {
                    $collection->addAttributeToFilter($attribute_code, array('in' => $attribute_value));
                } else {
                    $collection->addAttributeToFilter($attribute_code, array('like' => "%" . $_REQUEST[$attribute_code] . "%"));
                }
            }

            // We need to add compatible vehicles
            if ($currentVehicle = Mage::getSingleton('core/cookie')->get('currentVehicle')) {

                $vehicleIds = Mage::helper('unleaded_ymm')->getVehicleIdsFromSegment($currentVehicle);

                $filter = [];
                foreach ($vehicleIds as $vehicleId) {
                    $filter[] = [
                        'attribute' => 'compatible_vehicles',
                        'finset' => $vehicleId
                    ];
                }
                $collection->addAttributeToFilter($filter);
            }

            $collection->setStore(Mage::app()->getStore())
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->addStoreFilter()
                    ->addUrlRewrite();

            //Mage::log($collection->getSelect()->__toString());

            Mage::getSingleton('catalogsearch/advanced')->prepareProductCollection($collection);
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        }

        return $this;
    }

}