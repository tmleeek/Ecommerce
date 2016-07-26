<?php

class Unleaded_Vehicle_Model_Resource_Product_Indexer_Eav_Source 
	extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{
	protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $adapter    = $this->_getWriteAdapter();

        // prepare multiselect attributes
        if (is_null($attributeId)) {
            $attrIds    = $this->_getIndexableAttributes(true);
        } else {
            $attrIds    = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        // load attribute options
        $options = array();
        $select  = $adapter->select()
            ->from($this->getTable('eav/attribute_option'), array('attribute_id', 'option_id'))
            ->where('attribute_id IN(?)', $attrIds);
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // This was added in to account for custom source model because we really need to index vehicles
        foreach ($attrIds as $attributeId) {
            $attributeModel = Mage::getModel(Mage_Eav_Model_Entity::DEFAULT_ATTRIBUTE_MODEL)->load($attributeId);
            if ($attributeModel->getSource() instanceof Unleaded_Vehicle_Model_Source_Vehicle) {
                $sourceModelOptions = $attributeModel->getSource()->getAllOptions();
                foreach ($sourceModelOptions as $o) {
                    $options[$attributeId][$o["value"]] = true;         
                }
            }
        }

        // prepare get multiselect values query
        $productValueExpression = $adapter->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $adapter->select()
            ->from(
                array('pvd' => $this->getValueTable('catalog/product', 'varchar')),
                array('entity_id', 'attribute_id'))
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array('store_id'))
            ->joinLeft(
                array('pvs' => $this->getValueTable('catalog/product', 'varchar')),
                'pvs.entity_id = pvd.entity_id AND pvs.attribute_id = pvd.attribute_id'
                    . ' AND pvs.store_id=cs.store_id',
                array('value' => $productValueExpression))
            ->where('pvd.store_id=?',
                $adapter->getIfNullSql('pvs.store_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID))
            ->where('cs.store_id!=?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->where('pvd.attribute_id IN(?)', $attrIds);

        $statusCond = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('pvd.entity_id'),
            'website_field' => new Zend_Db_Expr('cs.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $i     = 0;
        $data  = array();

        $query = $select->query();
        while ($row = $query->fetch()) {
            $values = array_unique(explode(',', $row['value']));
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = array(
                        $row['entity_id'],
                        $row['attribute_id'],
                        $row['store_id'],
                        $valueId
                    );
                    $i ++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = array();
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);

        return $this;
    }
}