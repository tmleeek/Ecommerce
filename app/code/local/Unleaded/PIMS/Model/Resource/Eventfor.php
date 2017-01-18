<?php

class Unleaded_PIMS_Model_Resource_Eventfor extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unleaded_pims/eventfor', 'entity_id');
	}
}