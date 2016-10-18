<?php

class Unleaded_PIMS_Model_Resource_Eav_Attribute
	extends Mage_Catalog_Model_Resource_Eav_Attribute
{
	/**
     * Check is an attribute used in EAV index
     *
     * @return bool
     */
    public function isIndexable()
    {
        // exclude price attribute
        if ($this->getAttributeCode() == 'price') {
            return false;
        }

        if (!$this->getIsFilterableInSearch() && !$this->getIsVisibleInAdvancedSearch() && !$this->getIsFilterable()) {
            return false;
        }

        $backendType    = $this->getBackendType();
        $frontendInput  = $this->getFrontendInput();

        if ($backendType == 'int' && $frontendInput == 'select') {
            return true;
        } else if ($frontendInput == 'multiselect') {
            if ($backendType == 'varchar' || $backendType == 'text') {
                return true;
            }
        } else if ($backendType == 'decimal') {
            return true;
        }

        return false;
    }
}