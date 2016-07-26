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
 * YahooScreen Api Model
 */
class AW_Vidtest_Model_Api_Yahoo extends AW_Vidtest_Model_Api_Abstract {
    /**
     * Name of Application to Description of upload
     */
    const APP_NAME = 'aheadWorks-Vidtest-YahooscreenApi-1.0';

    /**
     * YahooScreen
     */
    const OUTPUT_URL = 'http://query.yahooapis.com/v1/public/yql?q=';
    const PLAYER_URL = 'https://screen.yahoo.com/';
    const OUTPUT_FORMAT = '&format=json';

    /**
     * YahooScreen Api Routre Key
     * @var string
     */
    protected $_apiCode = 'yahoo';

    /**
     * YahooScreen Adapter
     */
    protected $_yahooscreen = null;

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
     * @param int|string $time string
     * @return string
     */
    protected function _getParsedTime($time) {
        if ($time) {
            $pattern = '%^PY([\d]+)M([\d]+)D([\d]+)TH([\d]+)M([\d]+)S([\d]+)$%x';
            preg_match($pattern, $time, $matches);
            array_shift($matches);

            $match = false;
            for($i = 0; $i < count($matches); $i++) {

                if ($match && $matches[$i] == '0') $matches[$i] = '00';
                if ($matches[$i] > 0) $match = true;

            }

            if (($keys = array_keys($matches, '0', true)) !== false) {
                foreach($keys as $key) {
                    unset($matches[$key]);
                }

            }

            $duration = implode(':', $matches);
            return $duration;
        }
        return null;
    }

    /**
     * Rerives YahooScreen Adapter
     * @return object
     */
    protected function _getYahooScreen($apiVideoId) {
        if (!$this->_yahooscreen) {
            $http = new Varien_Http_Adapter_Curl();
            $config = array('timeout' => 30, 'header' => 0);
            $requestQuery = '';

            $http->setConfig($config);
            $http->write(Zend_Http_Client::GET, AW_Vidtest_Model_Api_Yahoo::OUTPUT_URL . $this->_getEncodeURIComponent($apiVideoId) . AW_Vidtest_Model_Api_Yahoo::OUTPUT_FORMAT, '1.1', array(), $requestQuery);

            $response = $http->read();
            $response = json_decode($response);

            $this->_yahooscreen = $response->query->results;

            $http->close();
        }
        return $this->_yahooscreen;
    }

    protected function _getEncodeURIComponent($apiVideoId) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
        return strtr(rawurlencode('select * from html where url="' . $this->getVideoUrl($apiVideoId, '') . '" and xpath="//title|//head/meta"'), $revert);
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
            $yahooscreen = $this->_getYahooScreen($apiVideoId);
            Varien_Profiler::start('aw::vidtest::vimeo::get_video_entry');

            if (!$yahooscreen) {
                $video->setRejected(true);
                return $video;
            }

            $attributes = array();
            foreach($yahooscreen->meta as $item) {
                $attribute = $this->_getMetaAttribute($item);
                if(count($attribute) > 0) $attributes = array_merge($attributes, $attribute);
            }

            $video->addData(array(
                'thumbnail_url' => $attributes['thumbnailUrl'],
                'video_url' => $attributes['embedURL'],
                'views' => 'N/A',
                'time' => $this->_getParsedTime($attributes['duration']),
                'title' => $attributes['og:title'],
                'description' => $attributes['description'],
            ));

            $video->setReady(true);

            return $video;
        } catch (Exception $e) {
            return new Varien_Object(array('error' => true, 'message' => $e->getMessage()));
        }

        return new Varien_Object();
    }

    public function _getMetaAttribute($item) {
        $attribute = array();
        if (isset($item->name)) {
            $attribute[$item->name] = $item->content;
        }
        if (isset($item->property)) {
            $attribute[$item->property] = $item->content;
        }
        if (isset($item->itemprop)) {
            $attribute[$item->itemprop] = $item->content;
        }
        return $attribute;
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
    public function getVideoUrl($apiVideoId, $embed = '?format=embed') {
        if ($apiVideoId) {
            return 'https://screen.yahoo.com/' . $apiVideoId . '.html';
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
