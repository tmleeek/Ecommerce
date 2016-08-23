<?php

class Unleaded_Productline_Model_Source_Parentcategory extends Mage_Eav_Model_Entity_Attribute_Source_Abstract 
{
    public function getAllOptions() 
    {
        if (is_null($this->_options)) {
            // Get store category root ids (minus the default)
            $categories = [];
            foreach (Mage::app()->getStores() as $store) {
                if ($store->getName() === 'Lund International') {
                    continue;
                }
                $category        = Mage::getModel('catalog/category')
                                    ->load($store->getRootCategoryId());

                $like = $category->getPath() . '/%';
                $childCategories = $category
                                    ->getCollection()
                                    ->addAttributeToSelect('name')
                                    ->addFieldToFilter('path', ['like' => $like])
                                    ->addFieldToFilter('level', 2);
                foreach ($childCategories as $childCategory) {
                    $categories[$childCategory->getId()] = $store->getName() . ' - ' . $childCategory->getName();
                }
            }

            if (count($categories > 0)) {
                $this->_options = [
                    [
                        'label' => '',
                        'value' => 0
                    ]
                ];

                foreach ($categories as $categoryId => $categoryName) {
                    $this->_options[] = [
                        'label' => $categoryName,
                        'value' => $categoryId
                    ];
                }
            }
        }
        return $this->_options;
    }

    public function toOptionArray() 
    {
        return $this->getAllOptions();
    }

    public function getValueLabelArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] === 0)
                continue;
            
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}