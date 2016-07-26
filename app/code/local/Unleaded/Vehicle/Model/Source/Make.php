<?php

class Unleaded_Vehicle_Model_Source_Make extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    const MAIN = 1;
    const OTHER = 2;

    public function getAllOptions() {
        if (is_null($this->_options)) {
            $yearAttribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'make');

            if ($yearAttribute->usesSource()) {
                $this->_options = [
                    [
                        'label' => Mage::helper('vehicle')->__('Select A Make'),
                        'value' => 0
                    ]
                ];

                $options = $yearAttribute->getSource()->getAllOptions(false);
                foreach ($options as $_option) {
                    $this->_options[] = [
                        'label' => $_option['label'],
                        'value' => $_option['label']
                    ];
                }
            }
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }
}
