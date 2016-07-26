<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.9
 * @build     1298
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Seo_Block_Review_Helper extends Mage_Review_Block_Helper
{
    public function getConfig()
    {
    	return Mage::getSingleton('seo/config');
    }

    public function getReviewsUrl()
    {
        if ($this->getConfig()->isEnabledReviewSeoUrls()) {
            $uri = $this->getProduct()->getUrlKey();
            if (!$uri) {
                $product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
                $uri = $product->getData('url_key');
            }
            return Mage::getUrl($uri.'/reviews');
        } else {
            return parent::getReviewsUrl();
        }
    }
}