<?php

class Unleaded_ProductLine_Model_Source_Attribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = [
                [
                    'label' => '',
                    'value' =>  0
                ]
            ];

            $productLines = Mage::getModel('unleaded_productline/productline')
                            ->getCollection();
            foreach ($productLines as $productLine) {
                $this->_options[] = [
                    'label' => $productLine->getName(),
                    'value' => $productLine->getId()
                ];
            }
        }
        return $this->_options;
    }
 
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}