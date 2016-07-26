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
 * DailyMotion Api Model
 */
class AW_Vidtest_Model_Api_Dailymotion extends AW_Vidtest_Model_Api_Abstract {
    /**
     * Name of Application to Description of upload
     */
    const APP_NAME = 'aheadWorks-Vidtest-DailymotionApi-1.0';

    /**
     * DailyMotion
     */
    const OUTPUT_URL = 'https://api.dailymotion.com/video/';
    const PLAYER_URL = 'http://www.dailymotion.com/embed/video/';
    const OUTPUT_FORMAT = '?fields=description,duration,thumbnail_url,title,id,views_total';

    /**
     * DailyMotion Api Routre Key
     * @var string
     */
    protected $_apiCode = 'dailymotion';

    /**
     * DailyMotion Adapter
     */
    protected $_dailymotion = null;

    /**
     * Class constructor
     */
    protected function _construct() {
        parent::_construct();
    }

    /**
     * Retrives app name
     * @return string
     */
    protected function _getAppName() {
        return self::APP_NAME;
    }

    /**
     * Retrives parsed time (mm:ss)
     * @param int|string $seconds Seconds
     * @return string
     */
    protected function _getParsedTime($seconds) {
        if ($seconds) {
            return (($seconds - $seconds % 60) / 60) . ':' . ((strlen($seconds % 60) > 1) ? $seconds % 60 : '0' . $seconds % 60 );
        }
        return null;
    }

    /**
     * Rerives DailyMotion Adapter
     * @return object
     */
    protected function _getDailyMotion($apiVideoId) {
        if (!$this->_dailymotion) {
            $http = new Varien_Http_Adapter_Curl();
            $config = array('timeout' => 30, 'header' => 0);
            $requestQuery = '';

            $http->setConfig($config);
            $http->write(Zend_Http_Client::GET, AW_Vidtest_Model_Api_Dailymotion::OUTPUT_URL . $apiVideoId . AW_Vidtest_Model_Api_Dailymotion::OUTPUT_FORMAT, '1.1', array(), $requestQuery);

            $response = $http->read();
            $response = json_decode($response);

            $this->_dailymotion = $response;

            $http->close();
        }
        return $this->_dailymotion;
    }

    /**
     * Retrives video data
     * @param string $apiVideoId
     * @return Varien_Object
     */
    public function getVideoData($apiVideoId) {
        try {
            $video = new Varien_Object(array('id' => $apiVideoId));
            Varien_Profiler::start('aw::vidtest::vimeo::get_video_entry');
            $dailymotion = $this->_getDailyMotion($apiVideoId);
            Varien_Profiler::start('aw::vidtest::vimeo::get_video_entry');

            if (!isset($dailymotion->id)) {
                $video->setRejected(true);
                return $video;
            }

            $video->addData(array(
                'thumbnail_url' => $dailymotion->thumbnail_url,
                'video_url' => AW_Vidtest_Model_Api_Dailymotion::PLAYER_URL . $dailymotion->id,
                'views' => $dailymotion->views_total ? $dailymotion->views_total : 0,
                'time' => $this->_getParsedTime($dailymotion->duration),
                'title' => $dailymotion->title,
                'description' => $dailymotion->description,
            ));

            $video->setReady(true);

            return $video;
        } catch (Exception $e) {
            return new Varien_Object(array('error' => true, 'message' => $e->getMessage()));
        }

        return new Varien_Object();
    }

    /**
     * Retrives url to video thumbnail
     * @param string $apiVideoId
     * @return string
     */
    public function getThumbnailUrl($apiVideoId) {
        if ($entry = $this->getVideoData($apiVideoId)) {
            return $entry->getVideoState()->getThumbnailUrl();
        }
        return '';
    }

    /**
     * Retrives Url to Video
     * @param string $apiVideoId
     * @return string
     */
    public function getVideoUrl($apiVideoId) {
        if ($apiVideoId) {
            return 'http://www.dailymotion.com/embed/video/' . $apiVideoId;
        }
        return '';
    }

    /**
     * Retrives View Count of Video
     * @param string $apiVideoId
     * @return int
     */
    public function getViews($apiVideoId) {
        if ($entry = $this->getPublicVideoData($apiVideoId)) {
            return $entry->getViews();
        }
        return 0;
    }

    /**
     * Retrives time of Video
     * @param string $apiVideoId
     * @return string
     */
    public function getTime($apiVideoId) {
        if ($entry = $this->getVideoData($apiVideoId)) {
            return $entry->getVideoState()->getTime();
        }
        return '0:00';
    }

    /**
     * Retrives Rendered Html Code of Player
     * Possibility to use options
     * @param
     * @param Varien_Object $options still not work
     * @return string
     */
    public function getRenderedPlayer($url, $options = null) {
        $str = '';
        $str .= '<iframe src="' . $url . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        return $str;
    }
}
