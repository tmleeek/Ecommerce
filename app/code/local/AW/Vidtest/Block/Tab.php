<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Vidtest_Block_Tab extends Mage_Core_Block_Template {

    const PRODUCT_VIEW_TEMPLATE = "aw_vidtest/product/view.phtml";
    const PRODUCT_RANDOM_TEMPLATE = "aw_vidtest/random.phtml";

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $configProductTabBlock = Mage::getStoreConfig('vidtest/general/tab');

        switch($configProductTabBlock) {
            case 2:
                $this->getTestimonialBlock('vidtest/product_view', self::PRODUCT_VIEW_TEMPLATE);
                break;
            case 1:
                $this->getTestimonialBlock('vidtest/random', self::PRODUCT_RANDOM_TEMPLATE);
                break;
        }

        return $this;
    }

    public function getTestimonialBlock($blockName, $blockTemplate)
    {
        $layout = $this->getLayout();
        $blockTitle = Mage::getStoreConfig('vidtest/general/title');

        $block = $layout->createBlock($blockName, 'aw-video-testimonial')
            ->setTemplate($blockTemplate)
            ->setTitle($blockTitle);

        if ($productInfoBlock = $layout->getBlock('product.info')) {
            $productInfoBlock->append($block, 'aw-video-testimonial');
            $layout->getBlock('aw-video-testimonial')->addToParentGroup('detailed_info');
        }
    }

}