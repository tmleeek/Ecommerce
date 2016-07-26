<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * Video service connector
 */
class AW_Vidtest_Model_Connector {

    /**
     * Retrives API Model for currnet service
     * @param string $apiCode
     * @return Mage_Core_Model_Abstract
     */
    public function getApiModel($apiCode) {
        if ($apiCode) {
            $apiModel = Mage::getSingleton('vidtest/api_' . $apiCode);
            if ($apiModel) {
                $apiModel->setLoginData($this->getLoginData($apiCode));
            }
            return $apiModel;
        }
        return null;
    }

    /**
     * Retrives Model's Login Datta from Config
     * @param string $apiCode
     * @return Varien_Object
     */
    public function getLoginData($apiCode) {
        if ($apiCode) {
            foreach ($this->getApiModelsData() as $modelData) {
                if ($modelData->getCode() == $apiCode) {
                    return $modelData;
                }
            }
        }
        return null;
    }

    /**
     * Retrives available Api Models to access to video servises
     * Result is array with Varien_Objects
     * @return array()
     */
    public function getApiModelsData() {
        $models = array();
        $store = Mage::app()->getStore();
        $fullPath = 'stores/' . $store->getCode() . '/vidtest';

        $node = Mage::getConfig()->getNode($fullPath);

        if ($node && is_array($node->asArray())) {
            foreach ($node->asArray() as $key => $value) {
                if (is_array($value)) {
                    $model = new Varien_Object($value);
                    if ($model->getIsApiModel() && $model->getApiModelCode()) {
                        $model->setCode($model->getApiModelCode());
                        $models[] = $model;
                    }
                }
            }
        }
        return $models;
    }

    /**
     * Retrives Api Models
     * @return array
     */
    public function getApiModels() {
        $models = array();
        foreach ($this->getApiModelsData() as $modelData) {
            $model = Mage::getSingleton('vidtest/api_' . $modelData->getCode());
            $model->setLoginData($modelData->getData());
            $models[] = $model;
        }
        return $models;
    }

}
