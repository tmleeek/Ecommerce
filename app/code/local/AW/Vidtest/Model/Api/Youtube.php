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

include_once Mage::getConfig()->getOptions()->getLibDir() . '/Google/Client.php';
include_once Mage::getConfig()->getOptions()->getLibDir() . '/Google/Service/YouTube.php';

/**
 * Youtube Api Model
 */
class AW_Vidtest_Model_Api_Youtube extends AW_Vidtest_Model_Api_Abstract {
    /**
     * Name of Application to Description of upload
     */
    const APP_NAME = 'aheadWorks-Vidtest-YouTubeApi-1.0';

    const YOUTUBE_CLIENT_ID_PATH = 'vidtest/youtube/client_id';
    const YOUTUBE_CLIENT_SECRET_PATH = 'vidtest/youtube/client_secret';
    const YOUTUBE_ACCESS_TOKEN_PATH = 'vidtest/youtube/access_token';
    const YOUTUBE_REFRESH_TOKEN_PATH = 'vidtest/youtube/refresh_token';
    const YOUTUBE_DEVELOPER_KEY_PATH = 'vidtest/youtube/developer_key';

    /**
     * Video Entryes Cache
     * @var array
     */
    protected $_entryCahce = array();

    /**
     * Youtube Api Routre Key
     * @var string
     */
    protected $_apiCode = 'youtube';

    /**
     * Http Client
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;

    protected $_publicHttpClient = null;

    /**
     * Youtube Adapter
     * @var Zend_Gdata_YouTube
     */
    protected $_youtube = null;

    /**
     * Class constructor
     */
    protected function _construct() {
        parent::_construct();

//        $this->_httpClient = $this->_getHttpClient();
    }

    /**
     * Retrives app name
     * @return string
     */
    protected function _getAppName() {
        return self::APP_NAME;
    }

    /**
     * Retrives Http Client
     * @return Zend_Http_Client
     */
    protected function _getHttpClient() {
        if (!$this->_httpClient) {
            $OAUTH2_CLIENT_ID = Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_CLIENT_ID_PATH);
            $OAUTH2_CLIENT_SECRET = Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_CLIENT_SECRET_PATH);

            $redirect = $this->_getRedirectUrl();
            $key = Mage::getSingleton('adminhtml/url')->getSecretKey('aw_vidtest_authsub','return');
            $scope = array('https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtube.upload');

            $client = new Google_Client();
            $client->setClientId($OAUTH2_CLIENT_ID);
            $client->setClientSecret($OAUTH2_CLIENT_SECRET);
            $client->setScopes($scope);
            $client->setState($key);
            $client->setRedirectUri($redirect);
            $client->setAccessType('offline');

            $this->_httpClient = $client;
        }
        return $this->_httpClient;
    }

    /**
     * Retrives redirect url
     * @return string
     */
    public function _getRedirectUrl() {
        if (Mage::getStoreConfig('web/url/use_store')) {
            $allStores = Mage::app()->getStores();
            $store = reset($allStores);
            $baseUrl = explode(DS, Mage::getBaseUrl());
            $baseUrlWithoutStoreCode = implode(DS, array_slice($baseUrl, 0, count($baseUrl) - 2));
            $redirect = $baseUrlWithoutStoreCode . DS . $store->getCode() . '/vidtest/youtube/redirect/';
        } else {
            $redirect = Mage::getUrl('vidtest/youtube/redirect/');
        }

        return $redirect;
    }

    /**
     * Retrives Http Client
     * @return Zend_Http_Client
     */
    public function getHttpClient() {
        return $this->_getHttpClient();
    }

    /**
     * Retrives Http Client
     * @return Zend_Http_Client
     */
    protected function _getPublicHttpClient() {
        if (!$this->_httpClient) {
            $DEVELOPER_KEY = Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_DEVELOPER_KEY_PATH);

            $client = new Google_Client();
            $client->setDeveloperKey($DEVELOPER_KEY);

            $this->_httpClient = $client;
        }
        return $this->_httpClient;
    }

    /**
     * Check if logged-in
     * @return boolean
     */
    public function isLoggedIn($access_token = null) {
        if (!Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_CLIENT_ID_PATH) || !Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_CLIENT_SECRET_PATH) || !Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_REFRESH_TOKEN_PATH)) return false;

        if ($access_token = Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_ACCESS_TOKEN_PATH)) {
            $this->_getHttpClient()->setAccessToken($access_token);

            if ($this->_getHttpClient()->isAccessTokenExpired()) {
                try {
                    $this->_getHttpClient()->refreshToken(Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_REFRESH_TOKEN_PATH));
                    $access_token = $this->_getHttpClient()->getAccessToken();
                } catch (Google_Service_Exception $e) {
                    Mage::helper('vidtest')->addCustomerError($e->getMessage());
                    return false;
                } catch (Exception $e) {
                    Mage::helper('vidtest')->addCustomerError($e->getMessage());
                    return false;
                }

                Mage::getConfig()->saveConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_ACCESS_TOKEN_PATH, $access_token);
            }
            return true;
        }
        return false;
    }

    /**
     * Log out from video service
     * @return boolean
     */
    public function logOut($token = null) {
        $access_token = $token ? $token : Mage::getStoreConfig(AW_Vidtest_Model_Api_Youtube::YOUTUBE_REFRESH_TOKEN_PATH);
        return $this->_getHttpClient()->revokeToken($access_token);
    }

    /**
     * Retrives Youtube Adapter
     * @return Zend_Gdata_YouTube
     */
    protected function _getYoutube() {
        if (!$this->_youtube) {
            $this->_youtube = new Google_Service_YouTube($this->_httpClient);
        }
        return $this->_youtube;
    }

    /**
     * Upload video to servise throw direct method.
     * Delete temporary file
     * Save Video on Video Service
     *
     * @param Varien_Object $file File on store server
     * @param Varien_Object $videoData Prepared date for service
     * @return boolean
     */
    public function uploadVideo($file, $videoData) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $this->_getHttpClient();

        $youtube = $this->_getYoutube();

        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($videoData->getTitle());
        $snippet->setDescription($videoData->getComment() ? $videoData->getComment() : Mage::helper('vidtest')->__('Uploaded by ' . $this->_getAppName()));
        $snippet->setCategoryId('22');  // People & Blogs

        $status = new Google_Service_YouTube_VideoStatus();
        $status->setPrivacyStatus('private');

        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $chunkSizeBytes = 1 * 1024 * 1024;

        $this->_httpClient->setDefer(true);

        try {
            $insertRequest = $youtube->videos->insert("status,snippet", $video);

            $media = new Google_Http_MediaFileUpload(
                $this->_httpClient,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize($file->getData('size'));

            $status = false;
            $handle = fopen($file->getData('tmp_name'), "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            $videoId = $status['id'];
        } catch (Google_Service_Exception $e) {
            Mage::helper('vidtest')->addCustomerError($e->getMessage());
            $this->_removeTempFile($file->getTmpName());
            return false;
        } catch (Exception $e) {
            Mage::helper('vidtest')->addCustomerError($e->getMessage());
            $this->_removeTempFile($file->getTmpName());
            return false;
        }
        $this->_removeTempFile($file->getTmpName());

        $this->_httpClient->setDefer(false);

        if ($videoId) {
            return $this->getVideoData($videoId);
        }
        return false;
    }

    /**
     * Retrives parsed time (mm:ss)
     * @param int|string $time Seconds
     * @return string
     */
    protected function _getParsedTime($time) {
        if ($time) {
            $dateIntervals = new DateInterval($time);

            $intervals = array();
            $selection = array('y', 'm', 'd', 'h', 'i', 's');
            foreach ($selection as $s) {
                array_push($intervals, (string) $dateIntervals->$s);
            }

            $match = false;
            for ($i = 0; $i < count($intervals); $i++) {
                if ($match && strlen($intervals[$i]) == 1) $intervals[$i] = '0' . $intervals[$i];
                if ($intervals[$i] > 0) $match = true;
            }

            if(!$match) {
                return '0:00';
            }

            if (($keys = array_keys($intervals, '0', true)) !== false) {
                foreach ($keys as $key) {
                    unset($intervals[$key]);
                }

            }
            $intervals = array_values($intervals);

            if (count($intervals) == 1) {
                $intervals[0] = strlen($intervals[0]) == 1 ? $intervals[0] = '0' . $intervals[0]: $intervals[0];
                array_unshift($intervals, '0');
            }

            $duration = implode(':', $intervals);
            return $duration;
        }
    }

    /**
     * Retrives video data
     * @param string $apiVideoId
     * @return Varien_Object
     */
    public function getPublicVideoData($apiVideoId) {
        try {
            Varien_Profiler::start('aw::vidtest::youtube::get_video_entry');
            $this->_getPublicHttpClient();

            $youtube = $this->_getYoutube();
            $listResponse = $youtube->videos->listVideos('snippet,contentDetails,status,statistics', array('id' => $apiVideoId));

            if (empty($listResponse)) {
                return new Varien_Object(array('error' => true, 'message' => 'Can\'t find a video with video id:' . $apiVideoId));
            } else {
                $entry = $listResponse[0];
            }

            if (is_null($entry->id)) {
                return new Varien_Object(array('error' => true, 'message' => 'Can\'t find a video with video id:' . $apiVideoId));
            }

            Varien_Profiler::start('aw::vidtest::youtube::get_video_entry');

            $video = new Varien_Object(array('id' => $apiVideoId));
            $video->addData(array(
                'thumbnail_url' => $entry->getSnippet()->getThumbnails()->getHigh()->getUrl(),
                'video_url' => $this->getVideoUrl($apiVideoId),
                'views' => $entry->getStatistics()->getViewCount() ? $entry->getStatistics()->getViewCount() : 0,
                'time' => $this->_getParsedTime($entry->getContentDetails()->getDuration()),
                'title' => $entry->getSnippet()->getTitle(),
                'description' => $entry->getSnippet()->getDescription(),
            ));

            # Get state
            $state = null;
            if ($control = $entry->getStatus()) {
                $state = $control->getUploadStatus();
            }

            if ($state == 'processed') {
                $video->setReady(true);
                return $video;
            } elseif ($state == 'uploaded') {
                $video->setProcessing(true);
                return $video;
            } elseif ($state == 'rejected') {
                $video->setRejected(true);
                return $video;
            } elseif ($state == 'failed') {
                $video->setFailed(true);
                return $video;
            } elseif ($state == 'deleted') {
                $video->setDeleted(true);
                return $video;
            }
        } catch (Exception $e) {
            return new Varien_Object(array('error' => true, 'message' => $e->getMessage()));
        }

        return new Varien_Object();
    }

    /**
     * Retrives video data
     * @param string $apiVideoId
     * @return Varien_Object
     */
    public function getVideoData($apiVideoId) {
        if (!$this->isLoggedIn()) {
            return new Varien_Object(array('error' => true, 'message' => 'Authentication failed'));
        }

        try {
            Varien_Profiler::start('aw::vidtest::youtube::get_video_entry');
            $this->_getHttpClient();

            $youtube = $this->_getYoutube();
            $listResponse = $youtube->videos->listVideos('snippet,contentDetails,status,statistics', array('id' => $apiVideoId));

            if (empty($listResponse)) {
                return new Varien_Object(array('error' => true, 'message' => 'Can\'t find a video with video id:' . $apiVideoId));
            } else {
                $entry = $listResponse[0];
            }

            if (is_null($entry->id)) {
                return new Varien_Object(array('error' => true, 'message' => 'Can\'t find a video with video id:' . $apiVideoId));
            }

            Varien_Profiler::start('aw::vidtest::youtube::get_video_entry');

            $video = new Varien_Object(array('id' => $apiVideoId));
            $video->addData(array(
                'thumbnail_url' => $entry->getSnippet()->getThumbnails()->getHigh()->getUrl(),
                'video_url' => $this->getVideoUrl($apiVideoId),
                'views' => $entry->getStatistics()->getViewCount() ? $entry->getStatistics()->getViewCount() : 0,
                'time' => $this->_getParsedTime($entry->getContentDetails()->getDuration()),
                'title' => $entry->getSnippet()->getTitle(),
                'description' => $entry->getSnippet()->getDescription(),
            ));

            # Get state
            $state = null;
            if ($control = $entry->getStatus()) {
                $state = $control->getUploadStatus();
            }

            if ($state == 'processed') {
                $video->setReady(true);
                return $video;
            } elseif ($state == 'uploaded') {
                $video->setProcessing(true);
                return $video;
            } elseif ($state == 'rejected') {
                $video->setRejected(true);
                return $video;
            } elseif ($state == 'failed') {
                $video->setFailed(true);
                return $video;
            } elseif ($state == 'deleted') {
                $video->setDeleted(true);
                return $video;
            }
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
            return $entry->getThumbnailUrl();
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
            return 'https://www.youtube.com/embed/' . $apiVideoId . '?modestbranding=1';
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
            return $entry->getTime();
        }
        return '0:00';
    }

    /**
     * Delete video on service
     * @param string $apiVideoId
     * @return boolean
     */
    public function deleteVideo($apiVideoId) {
        if (!$this->isLoggedIn()) {
            return new Varien_Object(array('error' => true, 'message' => 'Authentication failed'));
        }
        try {
            $this->_getHttpClient();

            $youtube = $this->_getYoutube(); 
            $youtube->videos->delete($apiVideoId);
        } catch (Exception $e) {
            return new Varien_Object(array('error' => true, 'message' => $e->getMessage()));
        }
        return new Varien_Object(array('success' => true));
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

    /**
     * Update video data on service
     * @param string $apiVideoId
     * @param Varien_Object $videoData
     * @param Varien_Object
     */
    public function updateVideo($apiVideoId, $videoData) {
        if (!$this->isLoggedIn()) {
            return new Varien_Object(array('error' => true, 'message' => 'Authentification failed'));
        }
        try {
            $this->_getHttpClient();

            $youtube = $this->_getYoutube();
            $listResponse = $youtube->videos->listVideos('snippet,contentDetails,status,statistics', array('id' => $apiVideoId));

            if (empty($listResponse)) {
                return new Varien_Object(array('error' => true, 'message' => 'Can\'t find a video with video id:' . $apiVideoId));
            } else {
                $entry = $listResponse[0];
            }

            $entry->getSnippet()->setTitle($videoData->getTitle());
            $entry->getSnippet()->setDescription($videoData->getDescription());

            $youtube->videos->update('snippet,contentDetails,status,statistics', $entry);
        } catch (Exception $e) {
            return new Varien_Object(array('error' => true, 'message' => $e->getMessage()));
        }
        return new Varien_Object(array('success' => true));
    }

}
