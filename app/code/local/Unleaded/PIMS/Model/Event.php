<?php

class Unleaded_PIMS_Model_Event extends Mage_Core_Model_Abstract
{
	const FTP_POLL_EVENT = 'ftp_poll';
	const IMPORT_START = 'import_start';
	const IMPORT_QUEUE = 'import_queue';

	const INITIATOR_SYSTEM = 'system';
	const INITIATOR_ADMIN = 'admin_user';
	const MODEL_IDENTIFIER = 'unleaded_pims/event';
		
	protected function _construct()
	{
		$this->_init('unleaded_pims/event');
	}

	public function attachMessage(Unleaded_PIMS_Model_Message $message)
	{
		$this->_attach('message', $message);
	}

	public function attachNewSystemMessage($message, $type = false)
	{
		$this->attachMessage(Mage::helper('unleaded_pims')->newSystemMessage($message, $type));
	}

	private function _attach($type, $model)
	{
		$key = $type . '_for';

		$modelFor = Mage::getModel('unleaded_pims/' . $type . 'for')->setData([
			$type . '_id'  => $model->getId(),
			$key . '_id'   => $this->getId(),
			$key . '_type' => 'unleaded_pims/event'
		])->save();
	}

	public function getMessages()
	{
		return $this->_getAttached('message');
	}

	private function _getAttached($type)
	{
		$collection = Mage::getModel('unleaded_pims/' . $type . 'for')
						->getCollection()
						->addFieldToFilter($type . '_for_id', $this->getId())
						->addFieldToFilter($type . '_for_type', self::MODEL_IDENTIFIER);

		$entityIds = [];
		foreach ($collection as $for) {
			$entityIds[] = $for->getData($type . '_id');
		}

		return Mage::getModel('unleaded_pims/' . $type)
				->getCollection()
				->addFieldToFilter('entity_id', ['in' => $entityIds]);
	}
}