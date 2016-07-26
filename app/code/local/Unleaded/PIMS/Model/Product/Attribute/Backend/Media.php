<?php

class Unleaded_PIMS_Model_Product_Attribute_Backend_Media
	extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{
	public function addImage(Mage_Catalog_Model_Product $product, $file,
        $mediaAttribute = null, $move = false, $exclude = true)
    {
        $file = realpath($file);

        if (!$file || !file_exists($file)) {
            Mage::throwException(Mage::helper('catalog')->__('Image does not exist.'));
        }

        Mage::dispatchEvent('catalog_product_media_add_image', array('product' => $product, 'image' => $file));

        $pathinfo = pathinfo($file);
        $imgExtensions = array('jpg','jpeg','gif','png');
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
            Mage::throwException(Mage::helper('catalog')->__('Invalid image file type.'));
        }

        $fileName       = Mage_Core_Model_File_Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = Mage_Core_Model_File_Uploader::getDispretionPath($fileName);
        $fileName       = $dispretionPath . DS . $fileName;

        /// Check if it exists, if it does, just use that
        $fullPath = Mage::getBaseDir('media') . '/catalog/product' . $fileName;
        if (!file_exists($fullPath)) {
       	// End of modifications
	        $fileName = $this->_getNotDuplicatedFilename($fileName, $dispretionPath);

	        $ioAdapter = new Varien_Io_File();
	        $ioAdapter->setAllowCreateFolders(true);
	        $distanationDirectory = dirname($this->_getConfig()->getTmpMediaPath($fileName));

	        try {
	            $ioAdapter->open(array(
	                'path'=>$distanationDirectory
	            ));

	            /** @var $storageHelper Mage_Core_Helper_File_Storage_Database */
	            $storageHelper = Mage::helper('core/file_storage_database');
	            if ($move) {
	                $ioAdapter->mv($file, $this->_getConfig()->getTmpMediaPath($fileName));

	                //If this is used, filesystem should be configured properly
	                $storageHelper->saveFile($this->_getConfig()->getTmpMediaShortUrl($fileName));
	            } else {
	                $ioAdapter->cp($file, $this->_getConfig()->getTmpMediaPath($fileName));

	                $storageHelper->saveFile($this->_getConfig()->getTmpMediaShortUrl($fileName));
	                $ioAdapter->chmod($this->_getConfig()->getTmpMediaPath($fileName), 0777);
	            }
	        }
	        catch (Exception $e) {
	            Mage::throwException(Mage::helper('catalog')->__('Failed to move file: %s', $e->getMessage()));
	        }
	    ///////
	    }
	    ///////

        $fileName = str_replace(DS, '/', $fileName);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);
        $position = 0;
        if (!is_array($mediaGalleryData)) {
            $mediaGalleryData = array(
                'images' => array()
            );
        }

        foreach ($mediaGalleryData['images'] as &$image) {
            if (isset($image['position']) && $image['position'] > $position) {
                $position = $image['position'];
            }
        }

        $position++;
        $mediaGalleryData['images'][] = array(
            'file'     => $fileName,
            'position' => $position,
            'label'    => '',
            'disabled' => (int) $exclude
        );

        $product->setData($attrCode, $mediaGalleryData);

        if (!is_null($mediaAttribute)) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }

    protected function _moveImageFromTmp($file)
    {
        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->_getConfig()->getMediaPath($file));
        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }
        /// Only modification here
        // $destFile = $this->_getUniqueFileName($file, $ioObject->dirsep());
        $destFile = $file;
        ///////////

        /** @var $storageHelper Mage_Core_Helper_File_Storage_Database */
        $storageHelper = Mage::helper('core/file_storage_database');

        if ($storageHelper->checkDbUsage()) {
            $storageHelper->renameFile(
                $this->_getConfig()->getTmpMediaShortUrl($file),
                $this->_getConfig()->getMediaShortUrl($destFile));

            $ioObject->rm($this->_getConfig()->getTmpMediaPath($file));
            $ioObject->rm($this->_getConfig()->getMediaPath($destFile));
        } else {
            $ioObject->mv(
                $this->_getConfig()->getTmpMediaPath($file),
                $this->_getConfig()->getMediaPath($destFile)
            );
        }

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }
}