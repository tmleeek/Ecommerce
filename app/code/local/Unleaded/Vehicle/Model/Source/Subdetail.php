<?php

class Unleaded_Vehicle_Model_Source_Subdetail extends Mage_Eav_Model_Entity_Attribute_Source_Abstract 
{
    public function getAllOptions() 
    {
        if (is_null($this->_options)) {
            $subDetailAttribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'sub_detail');

            if ($subDetailAttribute->usesSource()) {
                $this->_options = [
                    [
                        'label' => '',
                        'value' => 0
                    ]
                ];

                $options = $subDetailAttribute->getSource()->getAllOptions(false);
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

    public function toOptionArray() 
    {
        return $this->getAllOptions();
    }
}