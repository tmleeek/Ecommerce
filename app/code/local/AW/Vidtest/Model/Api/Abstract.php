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
 * Abstract Api Model
 */
class AW_Vidtest_Model_Api_Abstract extends Varien_Object implements AW_Vidtest_Model_Api_Interface {

    /**
     * Api Code
     * @var string
     */
    protected $_apiCode = 'abstract';

    /**
     * Api Connector Instance
     * @var AW_Vidtest_Model_Connector
     */
    protected $_connector = null;

    /**
     * Array with upload error descriptions
     * @var string
     */
    protected $_uploadErrors = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3',
        7 => 'Failed to write file to disk. Introduced in PHP 5.1.0',
        8 => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0',
    );

    /**
     * Move tmp uploaded file
     * @param string $filename File name
     * @return boolean
     */
    protected function _removeTempFile($filename) {
        if (file_exists($filename)) {
            try {
                unlink($filename);
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retrives Upload Error Description
     * @param int|string $errorCode
     * @return string
     */
    public function getUploadErrorDesc($errorCode) {
        if (isset($this->_uploadErrors[$errorCode])) {
            return $this->_uploadErrors[$errorCode];
        }
        return 'Unknown upload error';
    }

    /**
     * Retrives Api Code of current Model
     * @return string
     */
    public function getApiCode() {
        return $this->_apiCode;
    }

    /**
     * Login to video service
     * @param Varien_Object $loginData
     * @return boolean
     */
    public function logIn($loginData) {
        return true;
    }

    /**
     * Vlidate login data for possibility to use for login
     * @param Varien_Object $loginData
     * @return boolean
     */
    public function validateLogInData($loginData) {
        return true;
    }

    /**
     * Log out from video service
     * @return boolean
     */
    public function logOut($token = null) {
        return true;
    }

    /**
     * Retrives
     * @return boolean
     */
    public function isLoggedIn($token = null) {
        return true;
    }

    /**
     * Refresh access token
     * @return boolean
     */
    public function refreshSecret($token = null) {
        return true;
    }

    /**
     * Retrives video data of public video
     * @param string $apiVideoId
     * @return Varien_Object
     */
    public function getPublicVideoData($apiVideoId) {
        return new Varien_Object();
    }

    /**
     * Retrives video data
     * @param string $apiVideoId
     * @return Varien_Object
     */
    public function getVideoData($apiVideoId) {
        return new Varien_Object();
    }

    /**
     * Retrives url to video thumbnail
     * @param string $apiVideoId
     * @return string
     */
    public function getThumbnailUrl($apiVideoId) {
        return '';
    }

    /**
     * Retrives Url to Video
     * @param string $apiVideoId
     * @return string
     */
    public function getVideoUrl($apiVideoId) {
        return '';
    }

    /**
     * Retrives View Count of Video
     * @param string $apiVideoId
     * @return int
     */
    public function getViews($apiVideoId) {
        return 0;
    }

    /**
     * Retrives time of Video
     * @param string $apiVideoId
     * @return string
     */
    public function getTime($apiVideoId) {
        return '0:00';
    }

    /**
     * Retrives Rendered Html Code of Player
     * Possibility to use options
     * @param Varien_Object $options
     * @return string
     */
    public function getRenderedPlayer($url, $options = null) {
        try {
            $html = Mage::app()->getLayout()
                    ->createBlock('vidtest/show_player_render_' . $this->getApiCode())
                    ->addData($options ? $options->getData() : array())
                    ->toHtml();
        } catch (Exception $e) {
            $html = '';
        }
        return $html;
    }

    /**
     * Retrives Rendered Html Code of Form
     * Possibility to use options
     * @param Varien_Object $options
     * @return string
     */
    public function getRenderedForm($options = null) {
        try {
            $html = Mage::app()->getLayout()
                    ->createBlock('vidtest/show_form_render_' . $this->getApiCode())
                    ->addData($options ? $options->getData() : array())
                    ->toHtml();
        } catch (Exception $e) {
            $html = '';
        }
        return $html;
    }

    /**
     * Retrives Rendered Html Code of Copyright
     * Possibility to use options
     * @param Varien_Object $options
     * @return string
     */
    public function getRenderedCopyright($options = null) {
        try {
            $html = Mage::app()->getLayout()
                    ->createBlock('vidtest/show_copyright_render_' . $this->getApiCode())
                    ->addData($options ? $options->getData() : array())
                    ->toHtml();
        } catch (Exception $e) {
            $html = '';
        }
        return $html;
    }

    /**
     * Upload video to servise throw direct method.
     * Delete temporary file
     * Save Video on Video Service
     * 
     * @param string $filePath Path to temp file on server
     * @param Varien_Object $videoData Prepared date for service
     * @return boolean
     */
    public function uploadVideo($file, $videoData) {
        return true;
    }

    /**
     * Update video data on service
     * @param string $apiVideoId
     * @param Varien_Object $videoData
     * @param AW_Vidtest_Model_Api_Abstarct
     */
    public function updateVideo($apiVideoId, $videoData) {
        return $this;
    }

    /**
     * Resume upload to service
     * @param string $apiVideoId
     * @param Varien_Data $resumeData
     * @return boolean
     */
    public function resumeUpload($apiVideoId, $resumeData) {
        return true;
    }

    /**
     * Delete video on service
     * @param string $apiVideoId
     * @return boolean
     */
    public function deleteVideo($apiVideoId) {
        return true;
    }

}

