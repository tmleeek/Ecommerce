<?php

class Unleaded_Vehicle_Adminhtml_VehiclebackendController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->_title($this->__("UL Vehicle"));
        $this->renderLayout();
    }

}
