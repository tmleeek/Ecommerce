<?php

class Unleaded_YMM_Model_CatalogSearch_Resource_Advanced
	extends Mage_CatalogSearch_Model_Resource_Advanced
{
	/**
     * We added support for 'text' tables for multiselect attributes
     */
    public function prepareCondition($attribute, $value, $collection)
    {
        $condition = false;

        if (is_array($value)) {
            if (!empty($value['from']) || !empty($value['to'])) { // range
                $condition = $value;
            // This is what we changed here since some of the custom attributes are using the text table
            // } else if ($attribute->getBackendType() == 'varchar') { // multiselect
            } else if (in_array($attribute->getBackendType(), ['varchar', 'text'])) { // multiselect
                $condition = array('in_set' => $value);
            } else if (!isset($value['from']) && !isset($value['to'])) { // select
                $condition = array('in' => $value);
            }
        } else {
            if (strlen($value) > 0) {
                if (in_array($attribute->getBackendType(), array('varchar', 'text', 'static'))) {
                    $condition = array('like' => '%' . $value . '%'); // text search
                } else {
                    $condition = $value;
                }
            }
        }

        return $condition;
    }	
}