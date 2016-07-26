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


class AW_Vidtest_Helper_Data extends Mage_Core_Helper_Abstract {

    const YOUTUBE_API_CODE          = 'youtube';
    const VIMEO_API_CODE            = 'vimeo';
    const YAHOO_API_CODE            = 'yahoo';
    const DAILYMOTION_API_CODE      = 'dailymotion';
    const VK_API_CODE               = 'vk';

    static $API_CODES = array(
        self::YOUTUBE_API_CODE,
        self::VIMEO_API_CODE,
        self::YAHOO_API_CODE,
        self::DAILYMOTION_API_CODE,
        self::VK_API_CODE
    );

    /**
     * Retrives Rating Status
     * (Use constants from AW_Vidtest_Model_System_Config_Source_Rating)
     * @return int
     */
    public function confRatingStatus() {
        return Mage::getStoreConfig('vidtest/general/rating_status');
    }

    /**
     * Compare param $version with magento version
     * @param String $version
     * @return boolean
     */
    public function checkVersion($version) {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    /**
     * Retrives Cameled method name part (like YoWhoAreYou)
     * @param string $key Data key (like yo_who_are_you)
     * @return string
     */
    public function getDataKey($key) {
        if ($key) {
            $in = explode("_", $key);
            $out = array();
            foreach ($in as $el) {
                $out[] = ucwords(strtolower($el));
            }
            return implode("", $out);
        }
        return 'NullKey';
    }

    /**
     * Save value in customer session
     * @param string $key
     * @param mixed $value
     * @return AW_Vidtest_Helper_Data
     */
    public function setValue($key, $value) {
        $method = 'set' . $this->getDataKey($key);
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->$method($value);
        return $this;
    }

    /**
     * Load value from customer session
     * @param string $key
     * @return mixed
     */
    public function getValue($key) {
        $method = 'get' . $this->getDataKey($key);
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        return $session->$method();
    }

    /**
     * Output message to customer in known place
     * @param string $message Error message
     * @return AW_Vidtest_Helper_Data
     */
    public function addCustomerError($message) {
        Mage::getSingleton('core/session')->addError($this->__($message));
        return $this;
    }

    /**
     * Output message to customer in known place
     * @param string $message Success message
     * @return AW_Vidtest_Helper_Data
     */
    public function addCustomerSuccess($message) {
        Mage::getSingleton('core/session')->addSuccess($this->__($message));
        return $this;
    }

    public function uploadLimit() {

        $post = Mage::app()->getRequest()->getPost();
        if (empty($_FILES) && empty($post) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            return true;
        }
        if (!isset($_FILES['video_file'])) {
            return false;
        }
        $limit = ((int) Mage::getStoreConfig('vidtest/general/maxupload'));
        if (!$limit) {
            return false;
        }
        $currentSize = round($_FILES['video_file']['size'] / (1024 * 1024), 2);
        if ($currentSize > $limit) {
            return true;
        }
        return false;
    }

    /**
     * Retrives Can User Group upload file on servise
     * @return boolean
     */
    public function canUpload() {
        # Getting allowed for upload
        $groups = Mage::getStoreConfig('vidtest/general/allow_uploads');
        $groups = explode(",", $groups);
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $cGroup = $session->getCustomer()->getGroupId();
        } else {
            $cGroup = '0';
        }
        foreach ($groups as $group) {
            if (($group == $cGroup) || (!$group && !$cGroup))
                return true;
        }
        return false;
    }

    /**
     * Register rate in session for anticheat
     * @param int|string $videoId
     * @return AW_Vidtest_Helper_Data
     */
    public function registerRate($videoId) {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $rates = $session->getVidtestRates();
        if (count($rates)) {
            $rates[] = $videoId;
        } else {
            $rates = array($videoId);
        }
        $session->setVidtestRates($rates);

        return $this;
    }

    /**
     * Retrives (rate registered) flag
     * @param int|string $videoId
     * @return boolean
     */
    public function isRateRegistered($videoId) {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $rates = $session->getVidtestRates();
        if (isset($rates)) {
            return in_array($videoId, $rates);
        } else {
            return false;
        }
    }

    public function videoIdFromUrl($url='') {
        $pattern =
            '%^# Match any youtube URL
            (?:https?://)?                  # Optional scheme. Either http or https
            (?:www\.)?                      # Optional www subdomain
            (?:player\.                 # Optional vimeo prefix
            | screen\.)?                # or yahoo prefix
            (?:                             # Group host alternatives
              youtu\.be/                    # Either youtu.be,
              | youtube\.com                # or youtube.com
              | vimeo\.com                  # or vimeo.com
              | yahoo\.com                  # or yahoo.com
              | dailymotion\.com            # or dailymotion.com
              | dai\.ly                     # or dai.ly
              | vk\.com                     # or vk.com
            )                               # End host alternatives.
            (?:                             # Group path alternatives
                | /embed/                   # Either /embed/
                | /v/                       # or /v/
                | /watch\?v=                # or /watch\?v=
                | /video/                   # or /video/
                | /embed/video/             # or /embed/video/
                | /
            )                               # End path alternatives.
            ([\w-]{7,})                     # Allow 8 and more chars.
            (?:.*?)$%x'
            ;
        $result = preg_match($pattern, $url, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    public function getProviderFromUrl($url='') {
        $pattern =
            '%^# Match any youtube URL
            (?:https?://)?              # Optional scheme. Either http or https
            (?:www\.)?                  # Optional www subdomain
            (?:player\.                 # Optional vimeo prefix
            | screen\.)?                # or yahoo prefix
            (                           # Group host alternatives
              youtu\.be/                # Either youtu.be,
              | youtube\.com            # or youtube.com
              | vimeo\.com              # or vimeo.com
              | yahoo\.com              # or yahoo.com
              | dailymotion\.com        # or dailymotion.com
              | dai\.ly                 # or dai.ly
              | vk\.com                 # or vk.com
            )                           # End host alternatives.
            (?:.*?)$%x'
            ;
        $result = preg_match($pattern, $url, $matches);

        if (isset($matches[1])) {
            switch($matches[1]) {
                case 'youtu.be':
                case 'youtube.com':
                    return self::YOUTUBE_API_CODE;
                    break;
                case 'vimeo.com':
                    return self::VIMEO_API_CODE;
                    break;
                case 'yahoo.com':
                    return self::YAHOO_API_CODE;
                    break;
                case 'dailymotion.com':
                case 'dai.ly':
                    return self::DAILYMOTION_API_CODE;
                    break;
                case 'vk.com':
                    return self::VK_API_CODE;
                    break;
                default:
                    return false;
            }
        }
        return false;
    }

}