<?php

class Unleaded_PIMS_Helper_Data extends Mage_Core_Helper_Abstract
{
	const LOGFILE = 'unleaded_pims.log';

	protected $environment;
	protected $initiatorType;

	public function __construct()
	{
		$this->environment   = Mage::getStoreConfig('unleaded_pims/environment/name');
		$this->initiatorType = Unleaded_PIMS_Model_Event::INITIATOR_SYSTEM;
		$this->importHelper  = Mage::helper('unleaded_pims/import');
	}

	public function slugify($text) {
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		if (empty($text))
			return 'n-a';
		return $text;
	}

	public function log($message, $level)
	{
		Mage::log($message, $level, self::LOGFILE, true);
	}

	public function error($message)
	{
		$trace  = debug_backtrace();
		$header = $trace[1]['class'] . '->' . $trace[1]['function'] . '()' . ' LINE ' . $trace[0]['line'];
		$this->log($header . ' - ' . $message, Zend_Log::ERR);
		return false;
	}

	public function info($message)
	{
		$this->log($message, Zend_Log::INFO);
		return $this;
	}

	public function debug($message)
	{
		$this->log($message . ' ' . (memory_get_usage() / 1000000) . 'MB', Zend_Log::DEBUG);
		return $this;
	}

	public function warn($message)
	{
		$this->log($message, Zend_Log::WARN);
		return $this;
	}

	public function getEnvironment()
	{
		return $this->environment;
	}

	public function getRollbackFilename()
	{
		$rollbackDir = $this->importHelper->getRollbackDir();
		$fileName    = $this->environment . '.' . date('Ymd.Hi') . '.sql';
		return [
			'fullPath' => $rollbackDir . $fileName,
			'fileName' => $fileName
		];
	}

	public function isNotProduction()
	{	
		return !in_array(strtolower($this->getEnvironment()), ['production', 'prod']);
	}

	public function parseDataFilename($fileName)
	{
		// Magento[TYPE]_csv_[BRAND]_[OPERATION]_[TIMESTAMP].csv
		if (!preg_match('/^Magento([^_]*)_csv_([^_]*)_([^_]*)_([0-9]*)\.csv/', $fileName, $matches))
			throw new Exception('Unable to parse filename: ' . $fileName);

		return [
			'type'      => strtolower($matches[1]),
			'brand'     => strtolower($matches[2]),
			'operation' => strtolower($matches[3]),
			'timestamp' => $matches[4],
		];
	}

	public function newSystemMessage($messageBody = false, $type = false)
	{
		$message = Mage::getModel('unleaded_pims/message');
		$message
			->setInitiator($this->environment)
			->setInitiatorType($this->initiatorType);

		if ($message) {
			$message->setBody($messageBody);
		}

		if ($type) {
			$message->setType($type);
		} else {
			$message->setType(Unleaded_PIMS_Model_Message::TYPE_NOTE);
		}

		return $message->save();
	}

	public function newSystemEvent()
	{
		$event = Mage::getModel('unleaded_pims/event');
		return $event
				->setInitiator($this->environment)
				->setInitiatorType($this->initiatorType)
				->save();
	}

	public function newSystemEventWithMessage($messageBody, $type = false)
	{
		$event   = $this->newSystemEvent();
		$message = $this->newSystemMessage($messageBody, $type);

		$event->attachMessage($message);
		return $event;
	}
}