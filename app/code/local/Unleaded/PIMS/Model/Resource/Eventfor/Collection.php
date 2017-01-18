<?php

class Unleaded_PIMS_Model_Resource_Eventfor_Collection 
	extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unleaded_pims/eventfor');
	}
}