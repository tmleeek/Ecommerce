<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


class Unleaded_YMM_Model_Url_Builder extends Amasty_Shopby_Model_Url_Builder
{
	protected $currentVehicleCookie = null;
	public function getUrl()
    {
        $this->updateEffectiveQuery();

        $paramPart = $this->getParamPart();
        $basePart = $this->getBasePart($paramPart);

        $url = $basePart . $paramPart;

        $url = preg_replace('|(^:)/{2,}|', '$1/', $url);

        return $url;
    }

    public function getCurrentVehicleCookie()
    {
    	if ($this->currentVehicleCookie === null) {
    		$this->currentVehicleCookie = Mage::getSingleton('core/cookie')->get('currentVehicle');
    	}
    	return $this->currentVehicleCookie;
    }

    protected function getBasePart($paramPart)
    {
        $rootId = (int) Mage::app()->getStore()->getRootCategoryId();
        $reservedKey = Mage::getStoreConfig('amshopby/seo/key');
        $seoAttributePartExist = strlen($paramPart) && strpos($paramPart, '?') !== 0;

        $isSecure = Mage::app()->getStore()->isCurrentlySecure();
        $base = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $isSecure);

        if ($this->isCatalogSearch()){
            $url = $base . 'catalogsearch/result/';
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        elseif ($this->isNewOrSale()) {
            $url = $base . $this->moduleName;
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        elseif ($this->getCurrentLandingKey()) {
            $url = $base . $this->getCurrentLandingKey();

            if ($seoAttributePartExist) {
                $url.= '/';
            } else {
                $url = $this->getUrlHelper()->checkAddSuffix($url);
            }
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        elseif ($this->isCategorySearch()) {
            $url = $base . 'categorysearch/categorysearch/search/';
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        elseif ($this->moduleName == 'cms' && $this->getCategoryId() == $rootId) { // homepage,
            $hasFilter = false;
            if (Mage::getStoreConfig('amshopby/block/ajax')) {
                $hasFilter = true;
            }
            if (!$hasFilter) {
                foreach (array_keys($this->query) as $k){
                    if (!in_array($k, array('p','mode','order','dir','limit')) && false === strpos('__', $k)){
                        $hasFilter = true;
                        break;
                    }
                }
            }

            // homepage filter links
            if ($this->isUrlKeyMode() && $hasFilter){
                $url = $base . $reservedKey . '/';
            }
            // homepage sorting/paging url
            else {
                $url = $base;
            }
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        elseif ($this->getCategoryId() == $rootId) {
            $url = $base;
            switch ($this->mode) {
                case Amasty_Shopby_Model_Source_Url_Mode::MODE_DISABLED:
                    $needUrlKey = true;
                    break;
                case Amasty_Shopby_Model_Source_Url_Mode::MODE_MULTILEVEL:
                    $needUrlKey = !$this->isBrandPage();
                    break;
                case Amasty_Shopby_Model_Source_Url_Mode::MODE_SHORT:
                    $needUrlKey = !$seoAttributePartExist;
                    break;
                default:
                    $needUrlKey = true;
            }
            if ($needUrlKey) {
                $url.= $reservedKey;
                if ($seoAttributePartExist) {
                    $url .=  '/';
                }
            }
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }
        else { // we have a valid category
        	// We need to check if this is the 'All Products' category,
        	// if it is, and we have a current vehicle cookie set, we need to just send
        	// them to that vehicle page under /models/2016-vehicle-here_superduty
        	$category = $this->getCategoryObject();
        	if ($category->getName() === 'All Products' && $this->getCurrentVehicleCookie()) {
        		return $base . 'models/' . $this->getCurrentVehicleCookie();
        	}
            $url = $category->getUrl();
            $pos = strpos($url,'?');
            $url = $pos ? substr($url, 0, $pos) : $url;

            if ($seoAttributePartExist) {
                $url = $this->getUrlHelper()->checkRemoveSuffix($url);
                if ($this->isUrlKeyMode()) {
                    $url .= '/' . $reservedKey;
                }
                $url.= '/';
            }
            // echo __LINE__ . ' ';var_dump($url);echo '<br>';
        }

        if ($this->getCurrentVehicleCookie())
        	return $url . '/' . $this->getCurrentVehicleCookie();

        return $url;
    }
}