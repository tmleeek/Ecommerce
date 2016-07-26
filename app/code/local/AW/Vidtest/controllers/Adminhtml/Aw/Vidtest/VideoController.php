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
 * Adminhtml Video Grid/Edit Controller
 */
class AW_Vidtest_Adminhtml_Aw_Vidtest_VideoController extends Mage_Adminhtml_Controller_Action {

    /**
     * Init Grid Container
     * @return AW_Vidtest_Admin_VideoController
     */
    protected function _initAction() {
        if (Mage::helper('vidtest')->checkVersion('1.4.0.0')) {
            $this->_title($this->__('Catalog'))
                    ->_title($this->__('Video Testimonials'))
                    ->_title($this->__('Video'));
        }

        $this->loadLayout()
                ->_setActiveMenu('catalog/vidtest')
                ->_addBreadcrumb(Mage::helper('vidtest')->__('All videos'), Mage::helper('vidtest')->__('All videos'));

        return $this;
    }

    /**
     * Show all videos
     */
    public function indexAction() {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('vidtest/adminhtml_video'))
                ->renderLayout()
        ;
    }

    /**
     * Show pendign videos
     */
    public function pendingAction() {
        $this->_initAction()
                ->_addContent(
                        $this->getLayout()->createBlock('vidtest/adminhtml_video', '', array('pending' => true)))
                ->renderLayout()
        ;
    }

    /**
     * Update status to $status
     * @param int|string $id
     * @param int $status
     */
    protected function _updateStatus($id, $status) {
        $video = Mage::getModel('vidtest/video')->load($id);
        if ($video->getId() && ($status !== null)) {
            $video->setStatus($status);
            try {
                $video->save();
                $this->_addPointsForVideo($video);

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Update Title and Comment at Video Servise
     * @param string $apiCode Code of Api Model
     * @param string $videoId Id of video at service
     * @param string $title Title on video service
     * @param string $comment Comment on video service
     * @return string
     */
    protected function _updateDataOnYoutube($apiCode, $videoId, $title, $comment) {
        $apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($apiCode);
        if ($apiModel) {
            $ret = $apiModel->updateVideo($videoId, new Varien_Object(array('title' => $title, 'description' => $comment)));
            if ($ret->getSuccess()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Enable status of video
     * @param int|string $id
     * @return boolean
     */
    protected function _enableStatus($id) {
        return $this->_updateStatus($id, AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED);
    }

    /**
     * Disable status of video
     * @param int|string $id
     * @return boolean
     */
    protected function _disableStatus($id) {
        return $this->_updateStatus($id, AW_Vidtest_Model_Video::VIDEO_STATUS_DISABLED);
    }

    /**
     * Delete testimonial
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id) 
    {
        $video = Mage::getModel('vidtest/video')->load($id);
        if ($video->getId()) {
            try {
                $video->delete();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Delete testimonial and source
     * @param int|string $id
     * @return boolean
     */
    protected function _fulldelete($id) {
        $video = Mage::getModel('vidtest/video')->load($id);
        if ($video->getId()) {
            try {
                if (!$video->getReadOnly()) {
                    $apiModel = Mage::getSingleton('vidtest/connector')->getApiModel($video->getApiCode());
                    if ($apiModel) {
                        if ($video->getApiVideoId()) {
                            $delResult = $apiModel->deleteVideo($video->getApiVideoId());
                        }
                        if ($delResult->getError()) {
                            $this->_addError($video->getApiCode() ? $video->getApiCode() . ': ' . $delResult->getMessage() : $delResult->getMessage());
                            return false;
                        }
                    }
                }
                $video->delete();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add Success message for future show
     * @param string $message Message to show
     * @param mixed $value Value to show in string
     */
    protected function _addSuccess($message, $value = null) {
        $message = $value ? Mage::helper('vidtest')->__($message, $value) : Mage::helper('vidtest')->__($message);
        Mage::getSingleton('core/session')->addSuccess($message);
    }

    /**
     * Add Error message for future show
     * @param string $message Message to show
     * @param mixed $value Value to show in string
     */
    protected function _addError($message, $value = null) {
        $message = $value ? Mage::helper('vidtest')->__($message, $value) : Mage::helper('vidtest')->__($message);
        Mage::getSingleton('core/session')->addError($message);
    }

    /**
     * Edit form
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $ret = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY);
        $video = Mage::getModel('vidtest/video');
        if ($id) {
            $video->load($id);
        }
        if ($video->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $video->setData($data);
            }

            # Set up default rate and votes
            if (!$video->getRate() && !$video->getVotes()) {
                $video->setRate(0);
                $video->setVotes(0);
            }

            Mage::register(AW_Vidtest_Model_Form_Element_Player::REGISTRY_KEY, $video->getApiCode() ? $video->getApiCode() : 'youtube');

            if ($video->getState() == AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING) {
                $video->getReady();
            }
            
            $video->setData('comment', $video->getComment());
            
            Mage::register('video_data', $video);

            if (Mage::helper('vidtest')->checkVersion('1.4.0.0')) {
                $this->_title($this->__('Catalog'))
                        ->_title($this->__('Video Testimonials'));

                if ($id) {
                    $this->_title($this->__('Edit Video'));
                } else {
                    $this->_title($this->__('Add Video'));
                }
            }

            $this->loadLayout();
            $this->_setActiveMenu('catalog/vidtest');

            $this->_addBreadcrumb(Mage::helper('vidtest')->__('Video Testimonials'), Mage::helper('vidtest')->__('Video Testimonials'));
            $this->_addBreadcrumb(Mage::helper('vidtest')->__('Video edit'), Mage::helper('vidtest')->__('Video edit'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $form = $this->getLayout()->createBlock('vidtest/adminhtml_video_edit');
            if ($ret == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE) {
                $form->setIsPending(true);
            }
            $this->_addContent($form);
            $this->renderLayout();
        } else {
            $this->_addError('Testimonial not exists');
            $this->_redirect('*/*/' . (($ret == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE) ? 'pending' : ''));
        }
    }

    /**
     * Open seector of product for video review
     */
    public function newAction() {
        $id = $this->getRequest()->getParam('id');
        $ret = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY);


        if (Mage::helper('vidtest')->checkVersion('1.4.0.0')) {
            $this->_title($this->__('Catalog'))
                    ->_title($this->__('Video Testimonials'))
                    ->_title($this->__('Add Video'));
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/vidtest');

        $this->_addBreadcrumb(Mage::helper('vidtest')->__('Video Testimonials'), Mage::helper('vidtest')->__('Video Testimonials'));
        $this->_addBreadcrumb(Mage::helper('vidtest')->__('Video edit'), Mage::helper('vidtest')->__('Video edit'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $form = $this->getLayout()->createBlock('vidtest/adminhtml_product_selector');
        if ($ret == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE) {
            $form->setIsPending(true);
        }
        $this->_addContent($form);
        $this->renderLayout();
    }

    public function addAction() {
        $this->_forward('edit');
    }

    /**
     * Retrives Youtube Api Model
     * @param string $code Api Model Code
     * @return AW_Vidtest_Model_Api_Youtube
     */
    protected function _getApiModel($code) {
        return Mage::getSingleton('vidtest/connector')->getApiModel($code);
    }

    /**
     * Product Selector grid for AJAX request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('vidtest/adminhtml_product_selector_grid')->toHtml()
        );
    }

    /**
     * Save video
     */
    public function saveAction() {
        $id = $this->getRequest()->getParam('id');
        $back = $this->getRequest()->getParam('back');
        $pending = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY);

        $data = $this->getRequest()->getPost();

        $video = Mage::getModel('vidtest/video');

        # Save testimonial
        if ($id) {
            try {
                $video->load($id);


                if ($video->getId()) {
                    $video->addData($data);
                    try {
                        $video->save();

                        $this->_addPointsForVideo($video);

                        if (!$video->getReadOnly()) {
                            $this->_updateDataOnYoutube($video->getApiCode(), $video->getApiVideoId(), $video->getTitle(), $video->getComment());
                        }
                        $this->_addSuccess('Testimonial successfully saved');
                        if ($back) {
                            $this->_redirectReferer();
                        } elseif ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
                            $params = array(
                                'id' => $this->getRequest()->getParam('product_id'),
                                'store' => $this->getRequest()->getParam('store'),
                                'tab' => 'product_info_tabs_vidtest',
                            );
                            $this->_redirect('adminhtml/catalog_product/edit', $params);
                        } else {
                            $this->_redirect('*/*/' . (($pending == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE) ? 'pending' : ''));
                        }
                    } catch (Exception $e) {
                        $this->_addError('Saving failed with error: %s', $e->getMessage());
                        $this->_redirectReferer();
                    }
                } else {
                    $this->_addError('Saving failed');
                    $this->_redirectReferer();
                }
            } catch (Exception $e) {
                $this->_addError('Saving failed with error: %s', $e->getMessage());
                $this->_redirectReferer();
            }

        # Upload video and create testimonial
        } else {
            $file = new Varien_Object(isset($_FILES['video_file']) ? $_FILES['video_file'] : array());
            $data = new Varien_Object($data);

            if ($error = $file->getError() && $data->getVideoType() == 'file') {
                throw new Exception(Mage::helper('vidtest')->__($this->_getApiModel()->getUploadErrorDesc($error)));
                $this->_redirectReferer();
                return false;
            }

            $video = Mage::getModel('vidtest/video');

            # Set up basical info
            $video->setTitle($data->getTitle());
            $video->setComment($data->getComment());
            $video->setProductId($data->getProductId());
            $video->setAuthorName($data->getAuthorName());
            $video->setAuthorEmail($data->getAuthorEmail());
            $video->setRate($data->getRate());
            $video->setVotes($data->getVotes());
            //TODO Change this model

            if ($data->getSourceType() == AW_Vidtest_Model_System_Config_Source_Video_Sourcetype::SOURCE_LINK) {
                $provider = Mage::helper('vidtest')->getProviderFromUrl($data->getVideoLink());
            } else {
                $provider = AW_Vidtest_Helper_Data::YOUTUBE_API_CODE;
            }
            $video->setApiCode($provider);

            $video->setState(AW_Vidtest_Model_Video::VIDEO_STATE_PROCESSING);

            if (Mage::app()->isSingleStoreMode()) {
                # Get default store
                $defStore = 1;
                $stores = Mage::getModel('core/store')->getCollection();
                foreach ($stores as $store) {
                    if ($store->getId()) {
                        $data->setStores(array($store->getId()));
                    }
                }
            }
            $video->setStores($data->getStores());

            $video->setStatus($data->getStatus());

            try {
                if ($data->getSourceType() == AW_Vidtest_Model_System_Config_Source_Video_Sourcetype::SOURCE_LINK) {

                    $videoId = Mage::helper('vidtest')->videoIdFromUrl($data->getVideoLink());

                    if (!$videoId) {
                        throw new Exception(Mage::helper('vidtest')->__('The video url provided in the request is not correctly formatted.'));
                    }

                    $entry = $this->_getApiModel($video->getApiCode())->getVideoData($videoId);
                    // TODO check public video data for providers
                    if ($entry->getError() && $provider == 'youtube') {
                        $entry = $this->_getApiModel($video->getApiCode())->getPublicVideoData($videoId);
                        $video->setReadOnly(1);
                    }
                    $video->setState(AW_Vidtest_Model_Video::VIDEO_STATE_READY);
                } elseif ($data->getSourceType() == AW_Vidtest_Model_System_Config_Source_Video_Sourcetype::SOURCE_FILE) {
                    $entry = $this->_getApiModel($video->getApiCode())->uploadVideo($file, $data);
                } else {
                    $this->_addError('Post data corrupted');
                    $this->_redirectReferer();
                }

                if ($entry && $entry->getError()) {
                    throw new Exception($entry->getMessage());
                }
                if (!$entry) {
                    throw new Exception(Mage::helper('vidtest')->__('Answer of service not responsed. Try upload your video again.'));
                }

                if (!$entry->getVideoUrl()) {
                    //$this->_addError("You can't add this video. It's not for share");
                    // $this->_redirectReferer();
                    throw new Exception(Mage::helper('vidtest')->__("You can't add this video. It's not for share"));
                }

                $video->setApiVideoId($entry->getId());
                if ($entry->getReady()) {
                    $video->setApiVideoUrl($entry->getVideoUrl());
                    $video->setThumbnail($entry->getThumbnailUrl());
                    $video->setTime($entry->getTime());
                }

                $date = new Zend_Date();
                $video->setCreatedAt($date->toString('YYYY-MM-dd HH:mm'));

                $video->setIsNew(1);

                $video->save();
                $this->_addSuccess('Your video successfully added');

                if ($back) {
                    $this->_redirect('*/*/edit', array('id' => $video->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                $this->_addError($e->getMessage());
                $this->_redirectReferer();
            }
        }
    }

    /**
     * Enable Testimonial
     */
    public function enableAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            if ($this->_enableStatus($id)) {
                $this->_addSuccess('Status successfully updated');
            } else {
                $this->_addError('Status update failed');
            }
        }
        $this->_redirectReferer();
    }

    /**
     * Disable Testimonial
     */
    public function disableAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            if ($this->_disableStatus($id)) {
                $this->_addSuccess('Status successfully updated');
            } else {
                $this->_addError('Status update failed');
            }
        }
        $this->_redirectReferer();
    }

    /**
     * Delete Testimonial
     */
    public function deleteAction() {
        $from = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY);
        $pending = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY);

        if ($id = $this->getRequest()->getParam('id')) {
            if ($this->_delete($id)) {
                $this->_addSuccess('Testimonial successfully deleted');
            } else {
                $this->_addError('Testimonial delete failed');
            }
        }
        if ($from == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_EDIT) {
            $this->_redirect('*/*/' . ($pending ? 'pending' : ''));
        } elseif ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params = array(
                'id' => $this->getRequest()->getParam('product_id'),
                'tab' => 'product_info_tabs_vidtest',
                'store' => $this->getRequest()->getParam('store'),
            );
            $this->_redirect('adminhtml/catalog_product/edit', $params);
        } else {
            $this->_redirectReferer();
        }
    }

    /**
     * Delete Testimonial and video form YouTube
     */
    public function fulldeleteAction() {
        $from = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY);
        $pending = $this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY);

        if ($id = $this->getRequest()->getParam('id')) {
            if ($this->_fulldelete($id)) {
                $this->_addSuccess('Testimonial and source successfully deleted');
            } else {
                $this->_addError('Testimonial and source delete failed');
            }
        }
        if ($from == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_EDIT) {
            $this->_redirect('*/*/' . ($pending ? 'pending' : ''));
        } elseif ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params = array(
                'id' => $this->getRequest()->getParam('product_id'),
                'tab' => 'product_info_tabs_vidtest',
                'store' => $this->getRequest()->getParam('store'),
            );
            $this->_redirect('adminhtml/catalog_product/edit', $params);
        } else {
            $this->_redirectReferer();
        }
    }

    /**
     * Mass update status
     */
    public function massUpdateStatusAction() {
        $videos = $this->getRequest()->getParam('videos');
        $status = $this->getRequest()->getParam('status');
        if ($videos && is_array($videos) && ($status !== null)) {
            $success = $error = 0;
            foreach ($videos as $id) {
                if ($this->_updateStatus($id, $status)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_addSuccess('%s testimonial(s) successfully updated', $success);
            }
            if ($error) {
                $this->_addError('%s testimonial(s) update failed', $error);
            }
        } else {
            $this->_addError('Testimonial status update failed');
        }

        $this->_redirectReferer();
    }

    /**
     * Mass delete testimonials
     */
    public function massDeleteAction() {
        $videos = $this->getRequest()->getParam('videos');
        if ($videos && is_array($videos)) {
            $success = $error = 0;
            foreach ($videos as $id) {
                if ($this->_delete($id)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_addSuccess('%s testimonial(s) successfully deleted', $success);
            }
            if ($error) {
                $this->_addError('%s testimonial(s) delete failed', $error);
            }
        } else {
            $this->_addError('Testimonial delete failed');
        }
        $this->_redirectReferer();
    }

    /**
     * Mass delete testimonials and video sources
     */
    public function massFulldeleteAction() {
        $videos = $this->getRequest()->getParam('videos');
        if ($videos && is_array($videos)) {
            $success = $error = 0;
            foreach ($videos as $id) {
                if ($this->_fulldelete($id)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_addSuccess('%s testimonial(s) and video source(s) successfully deleted', $success);
            }
            if ($error) {
                $this->_addError('%s testimonial(s) and video source(s) delete failed', $error);
            }
        } else {
            $this->_addError('Testimonial(s) and video source(s) delete failed');
        }
        $this->_redirectReferer();
    }

    /**
     * Check admin permissions for this controller     *
     * @return boolean
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/vidtest');
    }

    protected function _addPointsForVideo($video) {
        if ($video && is_object($video)) {
            if ($video->getStatus() == AW_Vidtest_Model_Video::VIDEO_STATUS_ENABLED && $video->getIsNew()) {
                $video->setIsNew(0);
                $video->save();

                Mage::dispatchEvent('aw_points_vt_added', array('video' => $video));
            }
        }
    }

}