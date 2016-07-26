<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mage_integratee {

    public function __construct()
    {

    }

    public function product_url() {

        $_product_id = ee()->TMPL->fetch_param('product_id');

        if(!$_product_id) {
            return ee()->TMPL->no_results();
        }

        $_product = Mage::getModel('catalog/product')->load($_product_id);
        $temp = explode("?SID", $_product->getProductUrl());
        $_url = $temp[0];

        return $_url;

    }

    public function stars() {

        $_product_id = ee()->TMPL->fetch_param('product_id');

        if(!$_product_id) {
            return ee()->TMPL->no_results();
        }

        $_product = Mage::getModel('catalog/product')->load($_product_id);
        $storeId = Mage::app()->getStore()->getId();
        $summaryData = Mage::getModel('review/review_summary')
                    ->setStoreId($storeId)
                    ->load($_product->getId());

        if(!$summaryData->getReviewsCount()) {
            return ee()->TMPL->no_results();
        }

        $variables = array();
        $variables[] = $summaryData->getData();

        return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);

    }

    public function reviews() {

        $_product_id = ee()->TMPL->fetch_param('product_id');

        if(!$_product_id) {
            return ee()->TMPL->no_results();
        }

        $_product = Mage::getModel('catalog/product')->load($_product_id);

        $reviews = Mage::getModel('review/review')->getResourceCollection();
        $reviews->addStoreFilter( Mage::app()->getStore()->getId() )
              ->addStatusFilter( Mage_Review_Model_Review::STATUS_APPROVED )
              ->addEntityFilter('product', $_product_id)
              ->setDateOrder()
              ->addRateVotes()
              ->load();

        if(count($reviews->getItems()) == 0) {
            return ee()->TMPL->no_results();
        }

        $variables = array();

        $total = count($reviews->getItems());

        $i = 0;
        foreach($reviews->getItems() as $review):

            $variables[$i]['summary'] = $review->getTitle();
            $variables[$i]['nickname'] = $review->getNickname();
            $variables[$i]['review_date'] = strtotime($review->getCreatedAt());
            $variables[$i]['detail'] = $review->getDetail();
            $variables[$i]['review:count'] = $i + 1;
            $variables[$i]['review:total_results'] = $total;

            $j = 0;
            foreach($review->getRatingVotes() as $rating):

                if($rating->getReviewId() == $review->getReviewId()):
                    $variables[$i]['ratings'][$j]['code'] = $rating->getRatingCode();
                    $variables[$i]['ratings'][$j]['percent'] = $rating->getPercent();
                    $variables[$i]['ratings'][$j]['rating:count'] = $j + 1;
                endif;

                $j++;
            endforeach;

            $i++;
        endforeach;

        return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);

    }

    public function review_count() {

        $_product_id = ee()->TMPL->fetch_param('product_id');

        if(!$_product_id) {
            return ee()->TMPL->no_results();
        }

        $_product = Mage::getModel('catalog/product')->load($_product_id);

        $reviews = Mage::getModel('review/review')->getResourceCollection();
        $reviews->addStoreFilter( Mage::app()->getStore()->getId() )
              ->addStatusFilter( Mage_Review_Model_Review::STATUS_APPROVED )
              ->addEntityFilter('product', $_product_id)
              ->setDateOrder()
              ->addRateVotes()
              ->load();

        return count($reviews->getItems());

    }
}

/* End of file pi.mage_integratee.php */
/* Location: ./system/expressionengine/third_party/mage_integratee/pi.mage_integratee.php */