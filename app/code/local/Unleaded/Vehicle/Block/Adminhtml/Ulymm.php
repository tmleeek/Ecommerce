<?php

class Unleaded_Vehicle_Block_Adminhtml_Ulymm extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        $this->_controller = "adminhtml_ulymm";
        $this->_blockGroup = "vehicle";
        $this->_headerText = Mage::helper("vehicle")->__("Vehicle Manager");
        $this->_addButtonLabel = Mage::helper("vehicle")->__("Add New Vehicle");
        parent::__construct();
    }

}
