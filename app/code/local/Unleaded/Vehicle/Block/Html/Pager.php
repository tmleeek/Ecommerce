<?php

class Unleaded_Vehicle_Block_Html_Pager extends Mirasvit_Seo_Block_Html_Pager {

    public function getPageUrl($page) {
        //support of Mana_Filters
        if (Mage::helper('core')->isModuleEnabled('Mana_Filters') && Mage::helper('seo')->getFullActionCode() == 'catalog_category_view') {
            return parent::getPageUrl($page);
        }

        //support for Amasty Landing Pages
        if ($identifier = Mage::helper('seo')->isOnLandingPage()) {
            return $this->getParentBlock()->getPagerUrl(array($this->getPageVarName() => $page));
        }

        //support of TM_Attributepages, Fishpig_AttributeSplash and Magestore_Shopbybrand
        $excludedActions = array('attributepages_page_view', 'attributesplash_page_view', 'splash_page_view', 'brand_index_view', 'shopbybrand_index_view');
        if (in_array(Mage::helper('seo')->getFullActionCode(), $excludedActions)) {
            return parent::getPageUrl($page);
        }
        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_SeoFilter') && Mage::registry('current_category') && Mage::helper('seo')->getFullActionCode() != 'review_product_list' && Mage::helper('seo')->getFullActionCode() != 'tag_product_list') {
            if (Mage::getModel('seofilter/config')->isEnabled()) {
                if ($page == 1) {
                    $url = Mage::getModel('seofilter/catalog_layer_filter_item')->getSpeakingFilterUrl(FALSE, TRUE, array());

                    return $url;
                } else {
                    $url = Mage::getModel('seofilter/catalog_layer_filter_item')->getSpeakingFilterUrl(FALSE, TRUE, array($this->getPageVarName() => $page));

                    return $url;
                }
            }
        }

        if (Mage::helper('seo')->getFullActionCode() == 'review_product_list' || Mage::helper('seo')->getFullActionCode() == 'tag_product_list') {
            return $this->getSeoReviewUrl($page, false);
        } else {
            if ($page == 1) {
                $params = Mage::app()->getFrontController()->getRequest()->getQuery();
                unset($params['p']);

                $urlParams['_use_rewrite'] = true;
                $urlParams['_escape'] = true;
                $urlParams['_query'] = $params;

            } else {
                $urlParams = array();
                $urlParams['_current'] = true;
                $urlParams['_escape'] = true;
                $urlParams['_use_rewrite'] = true;
                $urlParams['_query'] = array($this->getPageVarName() => $page);
            }
            
            if (Mage::getSingleton('core/cookie')->get('currentVehicle')) {
                $turl = $this->getUrl('*/' . Mage::getSingleton('core/cookie')->get('currentVehicle'), $urlParams);
                $url = str_replace("/?", "?", $turl);
            } else {
                $url = $this->getUrl('*/*/*', $urlParams);
            }
            
            return $url;
        }
    }

}
