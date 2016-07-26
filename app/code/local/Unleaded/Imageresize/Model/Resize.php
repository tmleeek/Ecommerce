<?php

class Unleaded_Imageresize_Model_Resize extends Mage_Catalog_Model_Category {

    public function getResizedImage($width, $height = null, $thumbnail) {

        $_file_name = $thumbnail; // Here $cat is category data array                
        $_media_dir = Mage::getBaseDir('media') . '/' . 'catalog' . '/' . 'category' . '/';
        $cache_dir = $_media_dir . 'cache' . '/'; // Here i create a resize folder. for upload new category image
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir);
        }
        // set_error_handler(function() {

        // }, E_NOTICE | E_WARNING );
        try {
            if (getimagesize($cache_dir . $_file_name)) {
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache' . '/' . $_file_name;
                $image_size = getimagesize($catImg);

                if ($image_size[0] == $width && $image_size[1] == $height) {
                    return $catImg;
                } else {
                    unlink($cache_dir . $_file_name);
                    $_image = new Varien_Image($_media_dir . $_file_name);
                    $_image->constrainOnly(true);
                    $_image->keepAspectRatio(true);
                    $_image->keepFrame(false);
                    $_image->keepTransparency(true);
                    $_image->resize($width, $height);
                    $_image->save($cache_dir . $_file_name);
                    $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache' . '/' . $_file_name;
                    return $catImg;
                }
            } elseif (getimagesize($_media_dir . $_file_name)) {

                $_image = new Varien_Image($_media_dir . $_file_name);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(false);
                $_image->keepTransparency(true);
                $_image->resize($width, $height);
                $_image->save($cache_dir . $_file_name);
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache' . '/' . $_file_name;
                return $catImg;
            } else {
                $_file_name = "default_image.png";
                unlink($cache_dir . $_file_name);
                $_image = new Varien_Image($_media_dir . $_file_name);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(false);
                $_image->keepTransparency(true);
                $_image->resize($width, $height);
                $_image->save($cache_dir . $_file_name);
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache' . '/' . $_file_name;
                return $catImg;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        restore_error_handler();
    }




    public function getResizedImageHome($width, $height = null, $thumbnail) {

        $_file_name = $thumbnail; // Here $cat is category data array                
        $_media_dir = Mage::getBaseDir('media') . '/' . 'catalog' . '/' . 'category' . '/';
        $cache_dir = $_media_dir . 'cache_home' . '/'; // Here i create a resize folder. for upload new category image
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir);
        }
        // set_error_handler(function() {

        // }, E_NOTICE | E_WARNING );
        try {
            if (getimagesize($cache_dir . $_file_name)) {
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache_home' . '/' . $_file_name;
                $image_size = getimagesize($catImg);

                if ($image_size[0] == $width && $image_size[1] == $height) {
                    return $catImg;
                } else {
                    unlink($cache_dir . $_file_name);
                    $_image = new Varien_Image($_media_dir . $_file_name);
                    $_image->constrainOnly(true);
                    $_image->keepAspectRatio(true);
                    $_image->keepFrame(false);
                    $_image->keepTransparency(true);
                    $_image->resize($width, $height);
                    $_image->save($cache_dir . $_file_name);
                    $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache_home' . '/' . $_file_name;
                    return $catImg;
                }
            } elseif (getimagesize($_media_dir . $_file_name)) {

                $_image = new Varien_Image($_media_dir . $_file_name);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(false);
                $_image->keepTransparency(true);
                $_image->resize($width, $height);
                $_image->save($cache_dir . $_file_name);
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache_home' . '/' . $_file_name;
                return $catImg;
            } else {
                $_file_name = "default_image-home.png";
                unlink($cache_dir . $_file_name);
                $_image = new Varien_Image($_media_dir . $_file_name);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(false);
                $_image->keepTransparency(true);
                $_image->resize($width, $height);
                $_image->save($cache_dir . $_file_name);
                $catImg = Mage::getBaseUrl('media') . 'catalog' . '/' . 'category' . '/' . 'cache_home' . '/' . $_file_name;
                return $catImg;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        restore_error_handler();
    }

}
