<?php

class Unleaded_Vehicle_Block_Amasty_Advanced extends Amasty_Shopby_Block_Advanced {

    public function drawOpenCategoryItem($category, $level = 0) {
        if ($this->_isExcluded($category->getId()) || !$category->getIsActive()) {
            return '';
        }

        $cssClass = array(
            'amshopby-cat',
            'level' . $level
        );

        $currentCategory = $this->getDataHelper()->getCurrentCategory();

        if ($currentCategory->getId() == $category->getId()) {
            $cssClass[] = 'active';
        }

        if ($this->isCategoryActive($category)) {
            $cssClass[] = 'parent';
        }

        if ($category->hasChildren()) {
            $cssClass[] = 'has-child';
        }


        $productCount = '';
        if ($this->showProductCount()) {
            $productCount = $category->getProductCount();
            if ($productCount > 0) {
                $productCount = '&nbsp;<span class="count">(' . $productCount . ')</span>';
            } else {
                $productCount = '';
            }
        }

        $html = array();

        $presentParams = $this->getRequest()->getParams();
        if (!empty($presentParams)) {
            $paramString = "";
            $firstPoint = 0;
            foreach ($presentParams as $paramKey => $paramValue) {
                if($paramKey == 'brand'){
                    $firstPoint++;
                    continue;
                }
                
                $categoryUrl = Mage::helper('catalog/category')->getCategoryUrl($category);
                if ($firstPoint == 0) {
                    if(strpos($categoryUrl, "?")){
                        $paramString .= "&" . $paramKey . "=" . $paramValue;
                    } else {
                        $paramString .= "?" . $paramKey . "=" . $paramValue;
                    }
                    $firstPoint++;
                } else {
                    $paramString .= "&" . $paramKey . "=" . $paramValue;
                }
            }
            $html[1] = '<a href="' . Mage::helper('catalog/category')->getCategoryUrl($category) . $paramString . '">' . $this->htmlEscape($category->getName()) . $productCount . '</a>';
        } else {
            $html[1] = '<a href="' . Mage::helper('catalog/category')->getCategoryUrl($category) . '">' . $this->htmlEscape($category->getName()) . $productCount . '</a>';
        }

        $showAll = Mage::getStoreConfig('amshopby/advanced_categories/show_all_categories');
        $showDepth = Mage::getStoreConfig('amshopby/advanced_categories/show_all_categories_depth');

        $hasChild = false;

        $inPath = in_array($category->getId(), $currentCategory->getPathIds());
        $showAsAll = $showAll && ($showDepth == 0 || $showDepth > $level + 1);
        if ($inPath || $showAsAll) {
            $childrenIds = $category->getChildren();
            $children = $this->_getCategoryCollection()->addIdFilter($childrenIds);
            $this->_getFilterModel()->addCounts($children);
            $children = $this->asArray($children);

            if ($children && count($children) > 0) {
                $hasChild = true;
                $htmlChildren = '';
                foreach ($children as $child) {
                    $htmlChildren .= $this->drawOpenCategoryItem($child, $level + 1);
                }

                if ($htmlChildren != '') {
                    $cssClass[] = 'expanded';
                    $html[2] = '<ol>' . $htmlChildren . '</ol>';
                }
            }
        }

        $html[0] = sprintf('<li class="%s">', implode(" ", $cssClass));
        $html[3] = '</li>';

        ksort($html);

        if ($category->getProductCount() || ($hasChild && $htmlChildren)) {
            $result = implode('', $html);
        } else {
            $result = '';
        }

        return $result;
    }

}
