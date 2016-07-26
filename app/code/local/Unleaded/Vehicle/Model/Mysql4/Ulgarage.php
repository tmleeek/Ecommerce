<?php

class Unleaded_Vehicle_Model_Mysql4_Ulgarage extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init("vehicle/ulgarage", "garage_id");
    }

}
