<?php

class Unleaded_Vehicle_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar {

    public function getPagerUrl($params = array()) {
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;

        if (Mage::getSingleton('core/cookie')->get('currentVehicle')) {
            $turl = $this->getUrl('*/' . Mage::getSingleton('core/cookie')->get('currentVehicle'), $urlParams);
            $url = str_replace("/?", "?", $turl);
        } else {
            $url = $this->getUrl('*/*/*', $urlParams);
        }

        return $url;
    }

}
