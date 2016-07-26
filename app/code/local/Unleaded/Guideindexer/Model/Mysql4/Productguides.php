<?php

class Unleaded_Guideindexer_Model_Mysql4_Productguides extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('guideindexer/productguides', 'guideindexer_id');
    }

}
