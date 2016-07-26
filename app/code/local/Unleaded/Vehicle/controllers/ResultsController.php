<?php

class Unleaded_Vehicle_ResultsController extends Mage_Core_Controller_Front_Action
{
    public function forAction()
    {
        $this->loadLayout();
        try {
            $query = $this->getRequest()->getQuery();
            Mage::getSingleton('catalogsearch/advanced')->addFilters($query);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('catalogsearch/session')->addError($e->getMessage());
        }
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
}