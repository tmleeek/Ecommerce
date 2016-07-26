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

        $this->urlKeyCache[$category->getId()] = $category->getUrlKey();

        if (isset($this->urlCache[$category->getId()]))
            return $this->urlCache[$category->getId()];

        $url = $this->getBaseUrl();

        // If we have vehicle make sure to route through models/ and add vehicle
        if ($this->getCurrentVehicleCookie())
            $url .= 'models/' . $this->getCurrentVehicleCookie();

        // Have to do category next because it can be a segment or query param
        if (!$this->getCurrentVehicleCookie()) {
            // Sub category support
            if ($category->getLevel() === '5') {
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
        } else {
            // If we do have a vehicle, category becomes query parameter
            $url .= '?category=' . $category->getUrlKey();
        }

        // Now add brand, which will always be a query param
        $store = Mage::getSingleton('core/cookie')->get('store');
        if ($store && $store !== 'default') {
            // Make sure brand isn't already in url
            if (!strstr($url, 'brand=')) {
                // Now check if we've already started query params
                if (strstr($url, '?'))
                    $url .= '&brand=' . $store;
                else
                    $url .= '?brand=' . $store;
            }
        }
        
        $this->urlCache[$category->getId()] = $url;
        return $this->urlCache[$category->getId()];
    }
}