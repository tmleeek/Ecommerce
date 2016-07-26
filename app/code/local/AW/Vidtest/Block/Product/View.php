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


/**
 * Video Testimonials Product View Block
 */
class AW_Vidtest_Block_Product_View extends Mage_Core_Block_Template {
    /**
     * Path to block template
     */
    const VIEW_TEMPLATE = "aw_vidtest/product/view.phtml";

    /**
     * Save current product model instance
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * Cached collection
     * @var AW_Vidtest_Model_Mysql4_Video_Collection
     */
    protected $_collection = null;

    /**
     * Array of thumbnails count for each layout
     * @var array
     */
    protected $_count = array();

    /**
     * Default thumbnails count
     * @var int
     */
    protected $_defaultCount = 4;

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate(self::VIEW_TEMPLATE);
        if (Mage::registry('current_product')) {
            $this->_product = Mage::registry('current_product');
        }
    }

    /**
     * Before rendering html, but after trying to load cache
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml() {
        parent::_beforeToHtml();
        if (!$this->getProduct() && $this->getProductId()) {
            $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
        }
        return $this;
    }

    /**
     * Set up count of thumbnails for every type of layout
     * @param string $layout Layout code
     * @param int|string $count Count of thumbnails that we can show inline
     * @return AW_Vidtest_Block_Product_View
     */
    public function addColumnCountLayoutDepend($layout, $count) {
        $this->_count[$layout] = $count;
        return $this;
    }

    public function initRatioThumbsToLayout() {

        // Video count
        $this->addColumnCountLayoutDepend('empty', Mage::helper('vidtest/config')->getThumbsForEmptyColLayout());
        $this->addColumnCountLayoutDepend('one_column', Mage::helper('vidtest/config')->getThumbsForOneColLayout());
        $this->addColumnCountLayoutDepend('two_columns_left', Mage::helper('vidtest/config')->getThumbsForTwoColWlbLayout());
        $this->addColumnCountLayoutDepend('two_columns_right', Mage::helper('vidtest/config')->getThumbsForTwoColWrbLayout());
        $this->addColumnCountLayoutDepend('three_columns', Mage::helper('vidtest/config')->getThumbsForThreeColLayout());
    }

    public function addHandleForCurrentLayout() {

        if (!$this->getPageLayout()) {

            $layout = $this->getLayout();
            $currentPageRootTemplate = $layout->getBlock('root')->getTemplate();
            $pageLayouts = Mage::getSingleton('page/config')->getPageLayouts();

            foreach ($pageLayouts as $pageLayout) {
                if ($currentPageRootTemplate == $pageLayout->getTemplate()) {
                    $this->getLayout()->getUpdate()->addHandle($pageLayout->getLayoutHandle());
                }
            }
        }
    }

    /**
     * Retrieve thumbnails count
     * @return int
     */
    public function getThumbnailCount() {
        if (!$this->getData('thumbnail_count')) {
            $pageLayout = $this->helper('page/layout')->getCurrentPageLayout();
            if ($pageLayout && $this->getColumnCountLayoutDepend($pageLayout->getCode())) {
                $this->setData(
                        'thumbnail_count', $this->getColumnCountLayoutDepend($pageLayout->getCode())
                );
            } else {
                $this->setData('thumbnail_count', $this->_defaultCount);
            }
        }
        return (int) $this->getData('thumbnail_count');
    }

    /**
     * Remove thumbnails count depends on page layout
     * @param string $pageLayoutCode
     * @return AW_Vidtest_Block_Product_View
     */
    public function removeColumnCountLayoutDepend($pageLayoutCode) {
        if (isset($this->_count[$pageLayoutCode])) {
            unset($this->_count[$pageLayoutCode]);
        }

        return $this;
    }

    /**
     * Retrieve thumbnails count depends on page layout
     *
     * @param string $pageLayoutCode
     * @return int|boolean
     */
    public function getColumnCountLayoutDepend($pageLayoutCode) {
        if (isset($this->_count[$pageLayoutCode])) {
            return $this->_count[$pageLayoutCode];
        }
        return false;
    }

    /**
     * Retrieve current page layout
     * @return Varien_Object
     */
    public function getPageLayout() {
        return $this->helper('page/layout')->getCurrentPageLayout();
    }

    /**
     * Retrives show buttons flag
     * @return boolean
     */
    public function showButtons() {
        return!!(count($this->getCollection()) > $this->getThumbnailCount());
    }

    public function getButtons() {
        $buttons = array();

        $count = count($this->getCollection());
        $tCount = $this->getThumbnailCount();
        $id = 0;
        $isFirst = true;
        $isLast = false;
        while ($count > 0) {
            $count -= $tCount;
            if ($count <= 0) {
                $isLast = true;
            }
            $button = new Varien_Object(array(
                        'num' => (int) $id + 1,
                        'id' => (int) $id,
                        'left' => (int) $id,
                        'first' => $isFirst,
                        'last' => $isLast,
                    ));
            $buttons[] = $button;
            $id++;
            $isFirst = false;
        }
        return $buttons;
    }

    /**
     * Retrives current product model instance
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return $this->_product;
    }

    /**
     * Retrives Section Title
     * @return string
     */
    public function getSectionTitle() {
        return Mage::getStoreConfig('vidtest/general/title');
    }

    /**
     * Retrives Rating Status
     * (Use constants from AW_Vidtest_Model_System_Config_Source_Rating)
     * @return int
     */
    public function getRatingStatus() {
        return Mage::helper('vidtest')->confRatingStatus();
    }

    /**
     * Flag to show player content
     * @return boolean
     */
    public function hasVideos() {
        return!!count($this->getCollection());
    }

    /**
     * Retrives YouTube player boundle
     * @return string
     */
    public function getPlayer() {
        return Mage::getSingleton('vidtest/connector')->getApiModel('youtube')->getRenderedPlayer();
    }

    /**
     * Retrives YouTube form
     * @return string
     */
    public function getForm() {
        return Mage::getSingleton('vidtest/connector')->getApiModel('youtube')->getRenderedForm();
    }

    /**
     * Retrives video collection
     * @return AW_Vidtest_Model_Mysql4_Video_Collection
     */
    public function getCollection() {
        if (!$this->_collection) {
            # 1. Check video states
            Mage::helper('vidtest/processing')->check($this->getProduct()->getId());
            # 2. Prepare collection
            $this->_collection = Mage::getModel('vidtest/video')->getCollection()
                    ->addStatusFilter(AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED)
                    ->addStateFilter(AW_Vidtest_Model_Video::VIDEO_STATE_READY)
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addProductFilter($this->getProduct()->getId())
                    ->setOrderByRate()
            ;
        }
        return $this->_collection;
    }

    /**
     * Retirves Can Show Block flag
     * @return boolean
     */
    public function canShow() {
        return!$this->getProduct()->getVidtestEnabled();
    }

    /**
     * Retrives Can User Group upload file on servise
     * @return boolean
     */
    public function canUpload() {
        return Mage::helper('vidtest')->canUpload();
    }

    /**
     * Retrives Ajax Url
     * @return string
     */
    public function getAjaxUrl() {
        return $this->getUrl('vidtest/video/rate');
    }

    /**
     * Retrives Can Rate flag
     * @param int|string $id Video Id
     * @return int
     */
    public function getCanRate($id) {
        return (Mage::helper('vidtest')->isRateRegistered($id) ? 0 : 1)
                && ($this->getRatingStatus() == AW_Vidtest_Model_System_Config_Source_Rating::STATUS_DISPLAY_AND_RATE);
    }

    /**
     * Retrives show rate box flag
     * @return boolean
     */
    public function showRate() {
        return $this->getRatingStatus() != AW_Vidtest_Model_System_Config_Source_Rating::STATUS_DISABLED;
    }

    public function prepareToDisplay($text) {

        $escaped = htmlspecialchars($text);
        $escaped = nl2br($escaped);
        $escaped = preg_replace("/[\n\r]/", "", $escaped);

        return $escaped;
    }

    public function prepareLength($string, $len = '30') {

        if (!is_string($string)) {
            return 'Array';
        }

        if (strlen($string) > $len) {
            return substr(trim($string), 0, $len) . "...";
        }

        return $string;
    }

    public function prepareVideoUrls($url) {

        if (Mage::app()->getRequest()->getScheme() == 'https') {
            if (preg_match("#http://#is", $url)) {
                return preg_replace("#http://#is", "https://", $url);
            }
        }

        return $url;
    }

    public function getVideoSummaryMessage() {

        $totalNum = $this->getCollection()->count();

        if ($totalNum > 1) {
            return $this->__('%s Videos', $totalNum);
        }

        return $this->__('%s Video', $totalNum);
    }

}

