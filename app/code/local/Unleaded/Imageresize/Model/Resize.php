<?php

class Unleaded_Imageresize_Model_Resize extends Mage_Catalog_Model_Category 
{
    public function getResizedImage($width, $height = null, $fileName = 'default_image.png') 
    {
        // If they didn't include a filename, it is now placeholder.png, except in the case they send nothing!
        if ($fileName == '') {
            $fileName = 'default_image.png';
        }

        // mage_root/media/catalog/category/
        $mediaDir = Mage::getBaseDir('media') . '/catalog/category/';

        // mage_root/media/catalog/category/cache/
        $cacheDir = $mediaDir . 'cache/';

        // Make sure the cache directory exists
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir);
        }

        // Set up file paths
        $sourceFilePath = $mediaDir . $fileName;
        $cacheFilePath  = $cacheDir . $fileName;

        // This speeds things up when an image doesn't exist
        // set_error_handler(function() {
        // }, E_NOTICE | E_WARNING );

        try {
            // Do we have a file name and does the cached version exist?
            if (file_exists($cacheFilePath)) {
                // Check size of cached version
                $imageSize = getimagesize($cacheFilePath);
                // Mage::log(print_r($imageSize, true));

                // If size matches, serve cached image right away
                // if ($imageSize[0] == $width && $imageSize[1] == $height) {
                // 
                // The reason I changed it to || is because we are constraining the proportions below, meaning that
                // the image will be re-cached every single time unless it is the exact aspect ratio!!! Not good!
                if ($imageSize[0] == $width || $imageSize[1] == $height) {
                    return $this->removeBasePath($cacheFilePath);
                } else {
                    // Size doesn't match, delete cached image and we will re cache
                    unlink($cacheFilePath);
                }
            } else if (!file_exists($sourceFilePath)) {
                // If there is no cached file, and the source isn't there either, we need to send a placeholder
                return $this->getDefaultImagePath();
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

        // Now try to cache the image
        if ($this->cacheImage($width, $height, $sourceFilePath, $cacheFilePath)) {
            // Caching worked, return the cache path
            $returnFilePath = $cacheFilePath;
        } else {
            // Caching did not work, return the source path
            $returnFilePath = $sourceFilePath;
        }

        // Mage::log(print_r([
        //     'cacheFilePath'  => $cacheFilePath,
        //     'sourceFilePath' => $sourceFilePath,
        //     'returnFilePath' => $returnFilePath,
        //     'relativePath'   => $this->removeBasePath($returnFilePath)
        // ], true));

        // restore_error_handler();

        return $this->removeBasePath($returnFilePath);
    }

    private function getDefaultImagePath()
    {
        return $this->removeBasePath(Mage::getBaseDir('media') . '/catalog/category/default_image.png');
    }

    private function removeBasePath($filePath)
    {
        return str_replace(Mage::getBaseDir(), '', $filePath);
    }

    private function cacheImage($width, $height, $sourceFilePath, $cacheFilePath)
    {
        Mage::log('Caching image - ' . $width . ' x ' . $height . ' - ' . $sourceFilePath);

        try {
            $_image = new Varien_Image($sourceFilePath);
            $_image->constrainOnly(true);
            $_image->keepAspectRatio(true);
            $_image->keepFrame(false);
            $_image->keepTransparency(true);
            $_image->resize($width, $height);
            $_image->save($cacheFilePath);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}