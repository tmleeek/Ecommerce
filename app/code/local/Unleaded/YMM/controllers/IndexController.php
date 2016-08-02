<?php

class Unleaded_YMM_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$request = $this->getRequest();

		Mage::log($this->getVehicleSegment());

        try {
			$vehicleIds                   = Mage::helper('unleaded_ymm')->getVehicleIdsFromSegment($this->getVehicleSegment());
			$query                        = $this->getRequest()->getQuery();
			$query['compatible_vehicles'] = $vehicleIds;

            Mage::log($query);
            Mage::log(get_class(Mage::getSingleton('catalogsearch/advanced')));

            Mage::getSingleton('catalogsearch/advanced')->addFilters($query);

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('catalogsearch/session')->addError($e->getMessage());
        }

        $this->loadLayout();
        // var_dump(Mage::app()->getLayout()->getUpdate()->getHandles());exit;
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
	}

	private function getVehicleSegment()
	{
		$request = $this->getRequest();
		$uri     = $request->getRequestUri();

		if (preg_match('/^\/models\/([a-zA-Z0-9-_\/]*)/', $uri, $matches))
			return $matches[1];
		return false;
	}
}