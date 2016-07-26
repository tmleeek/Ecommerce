<?php

class Unleaded_PIMS_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this
        	->loadLayout()
        	->_setActiveMenu("unleaded_pims/pims")
        	->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Dashboard"), Mage::helper("adminhtml")->__("PIMS Dashboard"));
        return $this;
    }

    public function indexAction()
    {
    	$this->loadLayout();
        $this->renderLayout();
    }
}