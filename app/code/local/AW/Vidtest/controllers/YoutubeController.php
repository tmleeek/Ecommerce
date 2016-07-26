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
 * Youtube Upload Controller
 */
class AW_Vidtest_YoutubeController extends Mage_Core_Controller_Front_Action {
    /**
     * Getting new video status config path
     */
    const GET_STATUS_PATH = 'vidtest/general/new_video_status';

    /**
     * Retrives Youtube Api Model
     * @param string $apiCode
     * @return AW_Vidtest_Model_Api_Youtube
     */
    protected function _getApiModel($apiCode = 'youtube') {
        return Mage::getSingleton('vidtest/connector')->getApiModel($apiCode);
    }

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
     * Get customer video
     */
    public function postAction() {
        $data = new Varien_Object($this->getRequest()->getPost());
        $helper = Mage::helper('vidtest');

        if ($data->getAuthorEmail()) {
            if (!Zend_Validate::is($data->getAuthorEmail(), 'EmailAddress')) {
                $helper->addCustomerError('Invalid email address.');
                $this->_redirectReferer();
                return;
            }
        }

        $file = new Varien_Object(isset($_FILES['video_file']) ? $_FILES['video_file'] : array());

        if (!$helper->canUpload()) {
            $helper->addCustomerError('You can not upload video');
            $this->_removeTempFile($file->getTmpName());
            return $this->_redirectReferer();
        }

        if ($data->getExtData() == $helper->getValue('youtube_secret_key')) {
            # Clean key for next post
            //$helper->setValue('youtube_secret_key', null);

            $video = Mage::getModel('vidtest/video');

            # Set up basical info
            $video->setTitle(htmlspecialchars($data->getTitle()));
            $video->setComment($data->getComment());
            $video->setProductId($data->getProductId());
            $video->setAuthorName(htmlspecialchars($data->getAuthorName()));
            $video->setAuthorEmail($data->getAuthorEmail());

            $video->setStore(Mage::app()->getStore()->getId());
            $video->setState(AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING);

            # Set up access info
            if (Mage::getStoreConfig(self::GET_STATUS_PATH)) {
                $video->setStatus(AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED);
            } else {
                $video->setStatus(AW_Vidtest_Model_Video::VIDEO_STATUS_PENDING);
            }

            try {

                switch ($data->getVideoType()) {
                    case 'link':
                        $videoId = $helper->videoIdFromUrl($data->getVideoLink());

                        if (!$videoId) {
                            throw new Exception(Mage::helper('vidtest')->__('The video url provided in the request is not correctly formatted.'));
                        }

                        $provider = $helper->getProviderFromUrl($data->getVideoLink());

                        $video->setApiCode($provider);

                        if (!$videoId) {
                            $helper->addCustomerError('Invalid URL');
                            return $this->_redirectReferer();
                        }

                        $entry = $this->_getApiModel($video->getApiCode())->getVideoData($videoId);

                        if ($entry->getError()) {
                            $entry = $this->_getApiModel($video->getApiCode())
                                    ->getPublicVideoData($videoId);

                            if ($entry->getError()) {
                                $helper->addCustomerError('Invalid URL');
                                return $this->_redirectReferer();
                            }


                            $video->setReadOnly(1);
                            $video->setState(AW_Vidtest_Model_Video::VIDEO_STATE_READY);
                        }

                        break;
                    case 'file':
                        $video->setApiCode('youtube');

                        if ((@$_FILES['video_file']['error'] === 1) || $helper->uploadLimit()) {
                            $helper->addCustomerError('Upload file size is too large');
                            $this->_removeTempFile($file->getTmpName());
                            return $this->_redirectReferer();
                        }

                        if ($file->getError()) {
                            $helper->addCustomerError('Video upload error');
                            return $this->_redirectReferer();
                        }

                        $video->setStatus(AW_Vidtest_Model_Video::VIDEO_STATUS_PROCESSING);
                        $entry = $this->_getApiModel()->uploadVideo($file, $data);
                        break;

                    default:
                        break;
                }

                if (!$entry || !is_object($entry)) {
                    $helper->addCustomerError('The service is not responding. Try to upload your video again.');
                    return $this->_redirectReferer();
                }

                if ($entry->getError()) {
                    $helper->addCustomerError($entry->getMessage());
                }

                if (!$entry->getVideoUrl()) {
                    $helper->addCustomerError("You can't add this video. It's not for share");
                }

                $video->setApiVideoId($entry->getId());
                if (!$entry->getProcessing()) {
                    $video->setApiVideoUrl($entry->getVideoUrl());
                    $video->setThumbnail($entry->getThumbnailUrl());
                    $video->setTime($entry->getTime());
                }

                $date = new Zend_Date();
                $video->setCreatedAt($date->toString('YYYY-MM-dd HH:mm'));

                # customerId (for Points)
                $session = Mage::getSingleton('customer/session');
                if ($session->isLoggedIn()) {
                    $video->setCustomerId($session->getCustomer()->getId());
                }

                $video->setIsNew(1);
                $video->setUploadStoreId(Mage::app()->getStore()->getStoreId());

                $video->save();

                if (Mage::getStoreConfig(self::GET_STATUS_PATH)) {
                    if ($data->getVideoType() == 'link') {
                        $helper->addCustomerSuccess('Your video has been successfully added');

                        $video->setIsNew(0);
                        $video->save();

                        /*  points  */
                        Mage::dispatchEvent('aw_points_vt_added', array('video' => $video));
                    } elseif ($data->getVideoType() == 'file') {
                        $helper->addCustomerSuccess('Your video has been accepted for processing');
                    }
                } else {
                    $helper->addCustomerSuccess('Your video has been accepted for moderation');
                }
            } catch (Exception $e) {
                $helper->addCustomerError($e->getMessage());
            }
        }
        return $this->_redirectReferer();
    }

    protected function redirectAction() {
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');

        $secure = false;
        if (Mage::app()->getStore()->isAdminUrlSecure()) {
            $secure = true;
        }

        if ($code && $state)
            $this->_redirect('adminhtml/aw_vidtest_authsub/return', array('_secure' => $secure, 'key' => $state, '_query' => array('code' => $code, 'api_model_code' => 'youtube')));
    }
}
