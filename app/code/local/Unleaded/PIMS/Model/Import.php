<?php

class Unleaded_PIMS_Model_Import extends Mage_Core_Model_Abstract
{
	const STATUS_NEW        = 'new';
	const STATUS_PROCESSING = 'processing';
	const STATUS_ERROR      = 'error';
	const STATUS_CRON_READY = 'cron_ready';
	const STATUS_COMPLETE   = 'complete';

	const MODEL_IDENTIFIER = 'unleaded_pims/import';
	
	protected function _construct()
	{
		$this->_init('unleaded_pims/import');
	}

	public function attachMessage(Unleaded_PIMS_Model_Message $message)
	{
		$this->_attach('message', $message);
		return $this;
	}

	public function attachEvent(Unleaded_PIMS_Model_Event $event)
	{
		$this->_attach('event', $event);
		return $this;
	}

	private function _attach($type, $model)
	{
		$key = $type . '_for';

		$modelFor = Mage::getModel('unleaded_pims/' . $type . 'for');
		$modelFor->setData([
			$type . '_id'  => $model->getId(),
			$key . '_id'   => $this->getId(),
			$key . '_type' => 'unleaded_pims/import'
		])->save();
	}

	public function getMessages()
	{
		return $this->_getAttached('message');
	}

	public function getEvents()
	{
		return $this->_getAttached('event');
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