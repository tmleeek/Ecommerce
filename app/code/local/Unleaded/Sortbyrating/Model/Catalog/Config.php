<?php 

class Unleaded_Sortbyrating_Model_Catalog_Config extends Mage_Catalog_Model_Config {
     public function getAttributeUsedForSortByArray()
    {
        $options = array(
		'position'  => Mage::helper('catalog')->__('Position'),
		'rating_summary' => Mage::helper('catalog')->__('Sort by rating')
		
       );
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }
}