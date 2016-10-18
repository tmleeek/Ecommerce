<?php

class Unleaded_YMM_Model_CatalogSearch_Advanced extends Mage_CatalogSearch_Model_Advanced
{
	public $category = false;
	public function addFilters($values)
    {
        $attributes     = $this->getAttributes();
        $hasConditions  = false;
        $allConditions  = array();

        // Grab category
        $this->category = isset($values['category']) ? $this->getCategoryFromUrlKey($values['category']) : false;
        Mage::register('currentCategory', $this->category);

        foreach ($attributes as $attribute) {
            // Mage::log($attribute->getAttributeCode());
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if (!isset($values[$attribute->getAttributeCode()])) {
                continue;
            }
            $value = $values[$attribute->getAttributeCode()];
            if (!is_array($value)) {
                $value = trim($value);
            }

            if ($attribute->getAttributeCode() == 'price') {
                $value['from'] = isset($value['from']) ? trim($value['from']) : '';
                $value['to'] = isset($value['to']) ? trim($value['to']) : '';
                if (is_numeric($value['from']) || is_numeric($value['to'])) {
                    if (!empty($value['currency'])) {
                        $rate = Mage::app()->getStore()->getBaseCurrency()->getRate($value['currency']);
                    } else {
                        $rate = 1;
                    }
                    if ($this->_getResource()->addRatedPriceFilter(
                        $this->getProductCollection(), $attribute, $value, $rate)
                    ) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else if ($attribute->isIndexable()) {
                if (!is_string($value) || strlen($value) != 0) {
                    if ($this->_getResource()->addIndexableAttributeModifiedFilter(
                        $this->getProductCollection(), $attribute, $value)) {
                        $hasConditions = true;
                        $this->_addSearchCriteria($attribute, $value);
                    }
                }
            } else {
                $condition = $this->_prepareCondition($attribute, $value);
                if ($condition === false) {
                    continue;
                }
                // Mage::log(print_r($condition, true));
                $this->_addSearchCriteria($attribute, $value);

                $table = $attribute->getBackend()->getTable();
                if ($attribute->getBackendType() == 'static') {
                    $attributeId = $attribute->getAttributeCode();
                } else {
                    $attributeId = $attribute->getId();
                }
                $allConditions[$table][$attributeId] = $condition;
            }
        }
        // Mage::log(print_r($allConditions, true));
        if ($allConditions) {
            $this->getProductCollection()->addFieldsToFilter($allConditions);
            // Mage::log((string)$this->getProductCollection()->getSelect());
        } else if (!$hasConditions) {
            Mage::throwException(Mage::helper('catalogsearch')->__('Please specify at least one search term.'));
        }

        return $this;
    }

    protected function getCategoryFromUrlKey($categoryUrlKey)
    {
    	$category = Mage::getModel('catalog/category')->loadByAttribute('url_key', $categoryUrlKey);
    	return $category ? $category : false;
    }

    /**
     * Prepare product collection
     *
     * @param Mage_CatalogSearch_Model_Resource_Advanced_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function prepareProductCollection($collection)
    {
    	// Mage::log(__LINE__ . ' ' . var_export($this->category, true));

        $collection
        	->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->setStore(Mage::app()->getStore())
            ->addMinimalPrice()
            ->addTaxPercents()
            ->addStoreFilter();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        if ($this->category) {
        	$collection->addCategoryFilter($this->category);
        }

        return $this;
    }

    /**
     * Add data about search criteria to object state
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @param   mixed $value
     * @return  Mage_CatalogSearch_Model_Advanced
     */
    protected function _addSearchCriteria($attribute, $value)
    {
        $name = $attribute->getStoreLabel();

        if (is_array($value)) {
            if (isset($value['from']) && isset($value['to'])) {
                if (!empty($value['from']) || !empty($value['to'])) {
                    if (isset($value['currency'])) {
                        $currencyModel = Mage::getModel('directory/currency')->load($value['currency']);
                        $from = $currencyModel->format($value['from'], array(), false);
                        $to = $currencyModel->format($value['to'], array(), false);
                    } else {
                        $currencyModel = null;
                    }

                    if (strlen($value['from']) > 0 && strlen($value['to']) > 0) {
                        // -
                        $value = sprintf('%s - %s',
                            ($currencyModel ? $from : $value['from']), ($currencyModel ? $to : $value['to']));
                    } elseif (strlen($value['from']) > 0) {
                        // and more
                        $value = Mage::helper('catalogsearch')->__('%s and greater', ($currencyModel ? $from : $value['from']));
                    } elseif (strlen($value['to']) > 0) {
                        // to
                        $value = Mage::helper('catalogsearch')->__('up to %s', ($currencyModel ? $to : $value['to']));
                    }
                } else {
                    return $this;
                }
            }
        }

        if (($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect')
            && is_array($value)
        ) {
            foreach ($value as $key => $val) {
                //////// This is the modification we are making, for compatible vehicles we 
                /// want to make sure that we are not getting the sub_model and sub_detail
                /// back with the option text, so we pass an additional flag to the 
                /// custom source model
                /// Unleaded_Vehicle_Model_Source_Vehicle
                if ($attribute->getAttributeCode() === 'compatible_vehicles') {
                    $value[$key] = $attribute->getSource()->getShortenedOptionText($val);
                } else {
                    $value[$key] = $attribute->getSource()->getOptionText($val);
                }
                //////////////////////////////////////////////////////// 
                
                if (is_array($value[$key])) {
                    $value[$key] = $value[$key]['label'];
                }
            }
            $value = implode(', ', $value);
        } else if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
            $value = $attribute->getSource()->getOptionText($value);
            if (is_array($value))
                $value = $value['label'];
        } else if ($attribute->getFrontendInput() == 'boolean') {
            $value = $value == 1
                ? Mage::helper('catalogsearch')->__('Yes')
                : Mage::helper('catalogsearch')->__('No');
        }

        $this->_searchCriterias[] = array('name' => $name, 'value' => $value);
        return $this;
    }
}