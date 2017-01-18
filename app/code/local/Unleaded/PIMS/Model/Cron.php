<?php
class Unleaded_PIMS_Model_Cron
{
	const DATA_DIR_EMPTY     = 'There are no new .csv files';
	const DATA_DIR_NOT_EMPTY = 'New .csv files found';

	public function ftpPoll()
	{
		Mage::log('Checking FTP');

		// Create event
		$event = Mage::helper('unleaded_pims')->newSystemEvent();
		$event
			->setEventName(Unleaded_PIMS_Model_Event::FTP_POLL_EVENT)
			->save();

		try {
			// Get the directory listing
			$listing = Mage::helper('unleaded_pims/ftp')->getDataDirectoryListing();

			// Create new message for this event
			$message = Mage::helper('unleaded_pims')->newSystemMessage();

			$event->attachMessage($message);

			// There is a folder in here called 'processed' which will have legacy files
			// so the directory is 'empty' if there is only one item in it (processed folder)
			if (count($listing) === 1) {
				// There were no new files in the FTP directory
				$message->setBody(self::DATA_DIR_EMPTY)->save();
				return;
			}

			// There were new files, we need to process them
			$message->setBody(self::DATA_DIR_NOT_EMPTY)->save();

			foreach ($listing as $file) {
				// Skip processed files folder
				if ($file === 'processed')
					continue;

				// Get file type, brand, operation and timestamp;
				$parsed = Mage::helper('unleaded_pims')->parseDataFilename($file);

				// Create a new message for each file so we have record
				$message = Mage::helper('unleaded_pims')->newSystemMessage();
				$body    = 'New file found' . PHP_EOL . $file . PHP_EOL . PHP_EOL;
				foreach ($parsed as $key => $value) {
					$body .= ucwords($key) . ': ' . $value . PHP_EOL;
				}
				$message->setBody($body)->save();
				$event->attachMessage($message);

				// Download the file to the appropriate 'pending' folder
				Mage::helper('unleaded_pims/ftp')->downloadPIMSFile($parsed['type'], $file);

				// Move the file on the FTP server into the 'processed' folder
				Mage::helper('unleaded_pims/ftp')->movePIMSFileToProcessing($file);

				// Create new Import model, attach event
				$import = $this->getNewImport();
				$import
					->setFile($file)
					->setStoreCode($parsed['brand'])
					->setOperation($parsed['operation'])
					->setImportType($parsed['type'])
					->save()
					->attachEvent($event)
					->attachMessage($message);
			}
		} catch (Exception $e) {
			$message = Mage::helper('unleaded_pims')->newSystemMessage($e->getMessage() . PHP_EOL . $e->getTraceAsString(), Unleaded_PIMS_Model_Message::TYPE_ERROR);

			$event->attachMessage($message);
		}
	}

	public function processImports()
	{
		Mage::log('Processing imports');

		// First check to see if we are already processing any imports
		$importing = (bool)Mage::getModel('unleaded_pims/import')
							->getCollection()
							->addFieldToFilter('status', Unleaded_PIMS_Model_Import::STATUS_PROCESSING)
							->count();
		if ($importing) {
			Mage::log('Already importing a file, try again later');
			return;
		}

		// Grab the oldest import that's ready to process
		$this->import = Mage::getModel('unleaded_pims/import')
				->getCollection()
				->addFieldToFilter('status', Unleaded_PIMS_Model_Import::STATUS_CRON_READY)
				->setOrder('updated_at', 'ASC')
				->getFirstItem();

		if (!$this->import->getId()) {
			Mage::log('No imports are ready to process');
			return;
		}

		Mage::log('Found import ready to process with id ' . $this->import->getId());

		$event = Mage::helper('unleaded_pims')->newSystemEventWithMessage('Importing of data started', Unleaded_PIMS_Model_Message::TYPE_ACTION);
		$event->setEventName(Unleaded_PIMS_Model_Event::IMPORT_START)->save();
		$this->import
			->setStatus(Unleaded_PIMS_Model_Import::STATUS_PROCESSING)
			->attachEvent($event)
			->save();

		// Grab the importer and start
		$importer = Mage::helper('unleaded_pims/import');
		Mage::log('Trying to import');

		register_shutdown_function([$this, 'fatalErrorHandler']);
		try {
			switch ($this->import->getImportType()) {
				case 'parts';
					$importer
						->setPartsFile($this->import->getFile())
						->parts($this->import->getStoreCode());
					break;
				case 'brands';
					$importer
						->setBrandsFile($this->import->getFile())
						->brands($this->import->getStoreCode());
					break;
				default;
					throw new Exception('No import type set on import');
			}
		} catch (Exception $e) {
			Mage::log('Error while importing - caught');
			$message = Mage::helper('unleaded_pims')->newSystemMessage('Error while importing:' . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
			$this->import
				->setStatus(Unleaded_PIMS_Model_Import::STATUS_ERROR)
				->attachMessage($message)
				->save();
		}

		$this->import
				->setStatus(Unleaded_PIMS_Model_Import::STATUS_COMPLETE)
				->save();
	}

	public function fatalErrorHandler()
	{
		$error = error_get_last();

		if ($error !== null) {
			Mage::log('Error while importing - fatal');
			$message = Mage::helper('unleaded_pims')->newSystemMessage('Error while importing:' . PHP_EOL . print_r($error, true), Unleaded_PIMS_Model_Message::TYPE_ERROR);
			$this->import
				->setStatus(Unleaded_PIMS_Model_Import::STATUS_ERROR)
				->attachMessage($message)
				->save();
		}
	}

	protected function getNewImport()
	{
		$import = Mage::getModel('unleaded_pims/import');
		return $import
				->setStatus(Unleaded_PIMS_Model_Import::STATUS_NEW)
				->setEnvironment(Mage::helper('unleaded_pims')->getEnvironment());
	}
}