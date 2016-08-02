<?php

class Unleaded_Vehicle_Block_Result extends Mage_CatalogSearch_Block_Advanced_Result {

    protected function _prepareLayout() 
    {
        parent::_prepareLayout();
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => Mage::helper('catalogsearch')->__('Home'),
                'title' => Mage::helper('catalogsearch')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => Mage::helper('catalogsearch')->__('Search'),
                'link' => $this->getUrl('*')
            ))->addCrumb('search_result', array(
                'label' => Mage::helper('catalogsearch')->__('Results')
            ));
        }
        $titleLabel = Mage::helper('vehicle')->mapAttributeOptionIdToLabel('year', $this->getRequest()->getQuery('year')) . " " . 
                Mage::helper('vehicle')->mapAttributeOptionIdToLabel('make', $this->getRequest()->getQuery('make')) . " " . 
                Mage::helper('vehicle')->mapAttributeOptionIdToLabel('model', $this->getRequest()->getQuery('model'));
        $title = $this->__("Vehicle search results for: %s", $titleLabel);
        $this->getLayout()->getBlock('head')->setTitle($title);
    }

    public function getFormUrl()
    {
        return Mage::getModel('core/url')
                        ->setQueryParams($this->getRequest()->getQuery())
                        ->getUrl('*/search/', array('_escape' => true)); //URL: example.com/module_frontname/
    }

    public function getCategory()
    {
        return Mage::registry('currentCategory');
    }

    public function getStore()
    {
        return Mage::app()->getStore();
    }
}
