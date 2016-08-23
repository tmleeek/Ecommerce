<?php

class Unleaded_YMM_Helper_Category extends Mage_Catalog_Helper_Category
{
    protected $currentVehicleCookie = null;
    protected $baseUrl              = null;
    protected $urlCache             = [];
    protected $urlKeyCache          = [];
    
    public function getCurrentVehicleCookie()
    {
        if ($this->currentVehicleCookie === null)
            $this->currentVehicleCookie = Mage::getSingleton('core/cookie')->get('currentVehicle');
        
        return $this->currentVehicleCookie;
    }

    public function getBaseUrl()
    {
        if ($this->baseUrl === null) 
            $this->baseUrl = Mage::getBaseUrl();

        return $this->baseUrl;  
    }

    public function getCategoryUrl($category)
    {
        if (!$category instanceof Mage_Catalog_Model_Category)
            $category = Mage::getModel('catalog/category')->setData($category->getData());

        if (isset($this->urlCache[$category->getId()]))
            return $this->urlCache[$category->getId()];

        $url = $this->getBaseUrl();

        // Sub category support
        if ($category->getLevel() === '3') {

            // We need to get the parent category's url
            $path = explode('/', $category->getPath());

            // Minus 2 because of 0 index
            $parentCategoryId = $path[count($path) - 2];

            // Now check cache for url
            if (isset($this->urlCache[$parentCategoryId])) {
                $parentUrl = $this->urlCache[$parentCategoryId];
            } else {
                // Otherwise we have to load this category and get it's url
                $parentCategory = Mage::getModel('catalog/category')->load($parentCategoryId);
                $parentUrl      = $this->getCategoryUrl($parentCategory);
            }

            // Now we just replace the parent url key with parent url key plus child url key
            // Parent URL key will always be in cache at this point
            $search  = $this->urlKeyCache[$parentCategoryId];
            $replace = $search . '/' . $category->getUrlKey();
            $url     = str_replace($search, $replace, $parentUrl);

        } else {
            $url .= $category->getUrlKey();
        }

        // Now add brand, which will always be a query param and is dependent on the 
        // category's parent
        $parentCategory = $category->getParentCategory();

        if ($parentCategory && $parentCategory->getName() !== 'Lund International') {
            $url = $this->maybeAddBrandToUrl($url, strtolower($parentCategory->getName()));
        } else {
            // If this is a Lund International category, we should append the brand to the category
            // only if it is the only brand. Otherwise, we need to dump them into the umbrella category
            $brands = explode(',', $category->getCategoryBrands());

            if (count($brands) === 1) {
                $url = $this->maybeAddBrandToUrl($url, strtolower($brands[0]));
            } else {
                // If there are two or more brands, give them a url without a brand
                // No changes here
            }
        }
        
        $this->urlCache[$category->getId()] = $url;
        return $this->urlCache[$category->getId()];
    }

    protected function maybeAddBrandToUrl($url, $brand)
    {
        // Make sure brand isn't already in url
        if (!strstr($url, 'brand=')) {
            // Now check if we've already started query params
            if (strstr($url, '?'))
                $url .= '&brand=' . $brand;
            else
                $url .= '?brand=' . $brand;
        }
        return $url;
    }
}