<?php

class Unleaded_PIMS_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unleaded_pims/event', 'entity_id');
	}
}