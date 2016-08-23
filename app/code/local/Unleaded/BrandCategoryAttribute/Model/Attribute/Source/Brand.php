<?php

class Unleaded_BrandCategoryAttribute_Model_Attribute_Source_Brand 
	extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_options = null;

	public function getAllOptions($withEmpty = false)
	{
        if (is_null($this->_options)) {
            $this->_options = [];

            // Get the stores
            foreach (Mage::app()->getStores() as $store) {
            	if ($store->getCode() === 'default')
            		continue;
            	
            	$this->_options[] = [
            		'label' => $store->getName(),
            		'value' => $store->getCode()
            	];
            }
        }
        $options = $this->_options;
        if ($withEmpty)
            array_unshift($options, ['value'=>'', 'label'=>'']);

        return $options;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
 
        foreach ($options as $item)
            if ($item['value'] == $value)
                return $item['label'];

        return false;
    }

    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column        = [
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        ];
 
        if (Mage::helper('core')->useDbCompatibleMode()) {
            $column['type']     = 'int(10)';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_SMALLINT;
            $column['length']   = 10;
            $column['nullable'] = true;
            $column['comment']  = $attributeCode . ' column';
        }
 
        return [$attributeCode => $column];
    }

    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}