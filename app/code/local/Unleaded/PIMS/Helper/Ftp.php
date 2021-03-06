<?php

class Unleaded_PIMS_Helper_Ftp extends Unleaded_PIMS_Helper_Data
{
	const TEMP_IMAGE_PATH = 'import/images/';

	const FTP_PARTS_ROOT      = 'parts/';
	const FTP_CATEGORIES_ROOT = 'product_categories/';

	const LUND_DATA_DIRECTORY      = 'Data/';
	const LUND_PROCESSED_DIRECTORY = 'Data/processed/';

	private $_connection;

	private $host;
	private $port;
	private $username;
	private $password;

	protected $pathsDownloaded = [];

	public function __construct()
	{
		$this->host     = Mage::getStoreConfig('unleaded_pims/ftp/host');
		$this->port     = Mage::getStoreConfig('unleaded_pims/ftp/port');
		$this->username = Mage::getStoreConfig('unleaded_pims/ftp/username');
		$this->password = Mage::getStoreConfig('unleaded_pims/ftp/password');
	}

	protected function ftpConnect()
	{
		try {
			return $this
					->connect()
					->login()
					->setToPassive();
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}
	}

	protected function connect()
	{
		$this->_connection = ftp_connect($this->host, $this->port);
		if ($this->_connection)
			return $this;

		$this->error('Could not connect to FTP');
		return $this;
	}

	protected function login()
	{
		if (ftp_login($this->_connection, $this->username, $this->password))
			return $this;

		$this->error('Could not login to FTP');
		return $this;
	}

	protected function setToPassive()
	{
		if (ftp_pasv($this->_connection, true))
			return $this;

		$this->error('Could not set FTP to passive mode');
		return $this;
	}

	public function getDataDirectoryListing()
	{
		if (!$this->_connection)
			$this->ftpConnect();

		if (!$listing = ftp_nlist($this->_connection, self::LUND_DATA_DIRECTORY)) {
			$this->error('Unable to get FTP listing for directory: ' . self::LUND_DATA_DIRECTORY);
			return $listing;
		}

		$listing = array_map(function($value) {
			return str_replace(self::LUND_DATA_DIRECTORY, '', $value);
		}, $listing);

		return $listing;
	}

	public function getPartsImage($fileName)
	{
        // Make local and ftp path
        $localPath = Mage::getBaseDir('var') . '/' . self::TEMP_IMAGE_PATH . $fileName;
        $ftpPath = self::FTP_PARTS_ROOT . $fileName;
        $message = 'Image - ' . $fileName . ' - FTP - ' . $ftpPath . ' - Local - ' . $localPath;

		if (!$this->_connection)
			$this->ftpConnect();

		// Check cache
		if (isset($this->pathsDownloaded[$fileName])) {
			$this->debug('Using image in cache');
            return $this->pathsDownloaded[$fileName];
        } else {
        	$this->debug($message);
            $this->getFile($ftpPath, $localPath);
        }

		if (!$this->getFile($ftpPath, $localPath)) {
            return $this->error('Unable to get file');
        }

		// Add to cache
		$this->pathsDownloaded[$fileName] = $localPath;
		return $this->pathsDownloaded[$fileName];
	}

	public function getCategoryImage($fileName)
	{	
		if (!$this->_connection)
			$this->ftpConnect();

		// Check cache
		if (isset($this->pathsDownloaded[$fileName]))
			return $this->pathsDownloaded[$fileName];

		// Cateogry image saves directly to media
		$localPath = Mage::getBaseDir('media') . '/catalog/category/' . $fileName;

		$ftpPath = self::FTP_CATEGORIES_ROOT . $fileName;

		$message = 'Image - ' . $fileName . ' - FTP - ' . $ftpPath . ' - Local - ' . $localPath;
		$this->debug($message);

		if (!$this->getFile($ftpPath, $localPath))
			return $this->error('Unable to get file');

		// Add to cache
		$this->pathsDownloaded[$fileName] = $localPath;
		return $this->pathsDownloaded[$fileName];
	}

	public function getFile($ftpPath, $localPath)
	{
		try {
			// Check if file exists locally, if it does then just return it's path
			if (file_exists($localPath)) {
                return $localPath;
            }

			// First check that this file exists in ftp
			if (ftp_size($this->_connection, $ftpPath) === -1){
                return $this->error('File ' . $ftpPath . ' does not exist on FTP');
            }

			// Make sure we are still connnected, if not reconnect
			if (!ftp_nlist($this->_connection, '/')) {
				$this->warn('Connection lost, attempting to reconnect');
				if (!$this->_ftpConnect())
					return $this->error('Tried to reconnect but was unable');
			}

			// Download the file
			if (!ftp_get($this->_connection, $localPath, $ftpPath, FTP_BINARY)){
                return $this->error('Could not download ' . $ftpPath . ' from FTP');
            }

			return $localPath;
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}
	}

	public function downloadPIMSFile($type, $filename)
	{
		if (!$this->_connection)
			$this->ftpConnect();

		$base = Mage::getBaseDir('var') . '/';

		switch ($type) {
			case 'parts';
				$localPath = $base . Unleaded_PIMS_Helper_Import::PARTS_PENDING_CSV_DIR . $filename;
				break;
			case 'brands';
				$localPath = $base . Unleaded_PIMS_Helper_Import::BRANDS_PENDING_CSV_DIR . $filename;
				break;
			default;
				throw new Exception('Incorrect type supplied: ' . $type);
		}

		$ftpPath = self::LUND_DATA_DIRECTORY . $filename;

		$this->getFile($ftpPath, $localPath);
	}

	public function movePIMSFileToProcessing($filename)
	{
		if (!$this->_connection)
			$this->ftpConnect();

		$oldPath = self::LUND_DATA_DIRECTORY . $filename;
		$newPath = self::LUND_PROCESSED_DIRECTORY . $filename;

		if (!ftp_rename($this->_connection, $oldPath, $newPath))
			throw new Exception('Unable to move file to processing folder: ' . $filename);
	}

	private function deleteTempImages($dirPath = null)
	{
		$tempImagesBaseDir = Mage::getBaseDir('var') . '/' . self::TEMP_IMAGE_PATH;
		$dirPath           = $dirPath ? $dirPath : $tempImagesBaseDir;
		if (!is_dir($dirPath))
	        return $this->error($dirPath . ' must be a directory');

	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/')
	        $dirPath .= '/';

	    $files = glob($dirPath . '*', GLOB_MARK);
	    foreach ($files as $file) {
	        if (is_dir($file))
	            $this->deleteTempImages($file);
	        else
	            unlink($file);
	    }

	    if ($dirPath !== $tempImagesBaseDir)
	    	rmdir($dirPath);
	}

	public function __destruct()
	{
		if ($this->_connection)
			ftp_close($this->_connection);

		$this->deleteTempImages();
	}
}
