<?php

class Unleaded_YMM_Block_Catalog_Category_Grid 
	extends Mage_Core_Block_Template
{
	public function getCategories()
	{
		$category = Mage::registry('current_category');
		return $category->getChildrenCategories();
	}

	public function getCategoryUrl($category)
	{
		return Mage::helper('catalog/category')->getCategoryUrl($category);
	}
}