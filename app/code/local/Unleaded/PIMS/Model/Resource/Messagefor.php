<?php

class Unleaded_PIMS_Model_Resource_Messagefor extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unleaded_pims/messagefor', 'entity_id');
	}
}