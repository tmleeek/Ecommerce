<?php

class Unleaded_Guideindexer_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $category = Mage::getModel('catalog/category')->load(Mage::app()->getRequest()->getParam('categoryId'));

        $currentPointer = Mage::app()->getRequest()->getParam('currentPointer');
        $loadTill = Mage::app()->getRequest()->getParam('loadTill');

        $products = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('i_sheet')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('product_line')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addCategoryFilter($category)
                ->setPage($currentPointer, $loadTill);

        $layout = Mage::getSingleton('core/layout');
        $block = $layout
                ->createBlock('core/template')
                ->setData('guideCollection', $products)
                ->setTemplate('lund/parseguides.phtml');

        echo $block->toHtml();
    }

    public function updatesheetcntAction() {

        $productId = Mage::app()->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product) {
            if ($product->getISheetDownloads()) {
                $currentCnt = $product->getISheetDownloads();
            } else {
                $currentCnt = 0;
            }

            $currentCnt = $currentCnt + 1;

            $product->setISheetDownloads($currentCnt);
            $product->setUrlKey(false);
            $product->save();
        }
    }

}
