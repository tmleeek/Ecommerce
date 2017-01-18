<?php

class Unleaded_PIMS_Model_Message extends Mage_Core_Model_Abstract
{
	const TYPE_NOTE = 'note';
	const TYPE_ACTION = 'action';
	const TYPE_ERROR = 'error';

	const MODEL_IDENTIFIER = 'unleaded_pims/message';

	protected function _construct()
	{
		$this->_init('unleaded_pims/message');
	}
}