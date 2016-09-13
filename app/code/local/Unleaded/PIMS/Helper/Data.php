<?php

class Unleaded_PIMS_Helper_Data extends Mage_Core_Helper_Abstract
{
	const LOGFILE = 'unleaded_pims.log';

	public function slugify($text) {
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		// trim
		$text = trim($text, '-');
		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);
		// lowercase
//		$text = strtolower($text);
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
}