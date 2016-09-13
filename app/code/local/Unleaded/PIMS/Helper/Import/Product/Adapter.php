<?php

class Unleaded_PIMS_Helper_Import_Product_Adapter 
	extends Unleaded_PIMS_Helper_Data
{
	protected $productResource;

	private $_entity;
	private $_connection;

	public $brandMap = [
		'AVS'  => 'AVS',
		'LUND' => 'Lund'
	];

	public $attributeMap = [
		'year'                    => ['field' => 'Year', 					'options' => []],
		'make'                    => ['field' => 'Make', 					'options' => []],
		'model'                   => ['field' => 'Model', 					'options' => []],
		'sub_model'               => ['field' => 'SubModel', 				'options' => []],
		'sub_detail'              => ['field' => 'SubDetail', 				'options' => []],
		'bed_type'                => ['field' => 'Bed Type', 				'options' => []],
		'bed_length'              => ['field' => 'Bed Length', 				'options' => []],
		'color'                   => ['field' => 'Color', 					'options' => []],
		'dim_a'                   => ['field' => 'DIM_A', 					'options' => []],
		'dim_b'                   => ['field' => 'DIM_B', 					'options' => []],
		'dim_c'                   => ['field' => 'DIM_C', 					'options' => []],
		'dim_d'                   => ['field' => 'DIM_D', 					'options' => []],
		'dim_e'                   => ['field' => 'DIM_E', 					'options' => []],
		'dim_f'                   => ['field' => 'DIM_F', 					'options' => []],
		'dim_g'                   => ['field' => 'DIM_G', 					'options' => []],
		'pop_code'                => ['field' => 'POP Code', 				'options' => []],
		'i_sheet'                 => ['field' => 'I-Sheet', 				'options' => []],
		'country_of_manufacture'  => ['field' => 'Country of Origin', 		'options' => []],
		'flare_height'            => ['field' => 'Flare Height', 			'options' => []],
		'flare_tire_coverage'     => ['field' => 'Flare Tire Coverage', 	'options' => []],
		'vehicle_type'            => ['field' => 'Vehicle Type', 			'options' => []],
		'model_type'              => ['field' => 'Model Type', 				'options' => []],
		'width'                   => ['field' => 'Width', 					'options' => []],
		'length'                  => ['field' => 'Length', 					'options' => []],
		'height'                  => ['field' => 'Height', 					'options' => []],
		'finish'                  => ['field' => 'Finish', 					'options' => []],
		'style'                   => ['field' => 'Style', 					'options' => []],
		'material'                => ['field' => 'Material', 				'options' => []],
		'material_thickness'      => ['field' => 'Material Thickness', 		'options' => []],
		'sold_as'                 => ['field' => 'Sold As', 				'options' => []],
		'warranty'                => ['field' => 'Warranty', 				'options' => []],
		'liquid_storage_capacity' => ['field' => 'Liquid Storage Capacity', 'options' => []],
		'tube_shape'              => ['field' => 'Tube Shape', 				'options' => []],
		'tube_size'               => ['field' => 'Tube Size', 				'options' => []],
		'box_style'               => ['field' => 'Box Style', 				'options' => []],
		'box_opening_type'        => ['field' => 'Box Opening Type', 		'options' => []],
		'brand'                   => ['field' => 'Brand Short Code', 		'options' => []],
		'brand_short_code'        => ['field' => 'Brand Short Code', 		'options' => []],
		'part_saleable'           => ['field' => 'Part Saleable', 	     	'options' => []],
		'drilling_required'       => ['field' => 'Drilling Required',    	'options' => []],
		'upc_code'                => ['field' => 'UPC Code',            	'options' => []],
		'part_number'             => ['field' => 'Part Number',            	'options' => []],
		'product_line'            => ['field' => 'Product Line Short Code', 'options' => []],
	];

	public function __construct()
	{
		$this->productResource  = Mage::getResourceSingleton('catalog/product');

		$resourceModel          = Mage::getResourceModel('eav/entity_attribute');

		foreach ($this->attributeMap as $attributeCode => $attributeData){
            $this->attributeMap[$attributeCode]['id'] = $resourceModel->getIdByCode('catalog_product', $attributeCode);
        }

		$this->_entity     = new Mage_Eav_Model_Entity_Setup('core_setup');
		$this->_connection = $this->_entity->getConnection('core_read');
	}

	public function getMappedValue($attribute, $row)
	{
		switch ($attribute) {
			////// Standard
			case 'sku';
				return $this->getSku($row);
			case 'name';
				return $this->getName($row);
			case 'short_description';
				return $this->getShortDescription($row);
			case 'description';
				return $this->getShortDescription($row);
			case 'status';
				return $this->getStatus($row);
			case 'url_key';
				return $this->getUrlKey($row);
			case 'weight';
				return $this->getWeight($row);
			case 'price';
				return $row['MSRP Price'];
			case 'msrp';
				return $row['MSRP Price'];
			case 'tax_class_id';
				return 2;
			case 'country_of_manufacture';
				return $this->countryOfManufacture($attribute, $row);
			case 'meta_title';
				return $this->getMetaTitle($row);
			case 'meta_description';
				return $this->getMetaDescription($row);

			////// Custom Attributes
			
			// Selects
			case 'pop_code';
			case 'i_sheet';
			case 'model_type';
			case 'flare_tire_coverage';
			case 'flare_height';
			case 'width';
			case 'length';
			case 'height';
			case 'finish';
			case 'style';
			case 'material';
			case 'material_thickness';
			case 'sold_as';
			case 'warranty';
			case 'liquid_storage_capacity';
			case 'tube_shape';
			case 'tube_size';
			case 'box_style';
			case 'box_opening_type';
			case 'brand_short_code';
				return $this->getOptionId($attribute, $row);

			case 'bed_type';
			case 'bed_length';
			case 'color';
				return $this->getOptionIdWithNA($attribute, $row);

			// Boolean
			case 'drilling_required';
				return $row['Drilling Required'] === '1' ? true : false;
			case 'oversize_shipping_required';
				return $row['Oversize Shipping Required'] === '1' ? true : false;
			case 'part_saleable';
				return $row['Part Saleable'] === '1' ? true : false;

			case 'brand';
				return $this->brandMap[$row['Brand Short Code']];

			// Text
			case 'part_number';
			case 'upc_code';
				return $row[$this->attributeMap[$attribute]['field']];

			// Custom
			case 'product_line';
				return $this->getProductLine($row);

			case 'dim_a';
			case 'dim_b';
			case 'dim_c';
			case 'dim_d';
			case 'dim_e';
			case 'dim_f';
			case 'dim_g';
				return $this->getDimension($row, $attribute);

			default;
				return null;
		}
	}

	protected function getName($row)
	{
		$name = $row['Product Line Short Code'];
		return preg_replace('/\s+/', ' ', $name);
	}

	protected function getProductLine($row)
	{
		// Get the value
		$field = $this->attributeMap['product_line']['field'];
		$value = $row[$field];

		// See if the value is in the cache
		if (isset($this->attributeMap['product_line']['options'][$value]))
			return $this->attributeMap['product_line']['options'][$value];

		// Otherwise load it and throw it in the cache
		$productLine = Mage::getModel('unleaded_productline/productline')
						->getCollection()
						->addFieldToFilter('product_line_short_code', $value)
						->getFirstItem();

		if (!$productLine || !$productLine->getId()) {
			$this->error('Unable to find product line for ' . $this->getMappedValue('sku', $row));
			return null;
		}

		return $productLine->getId();
	}

	protected function getMetaDescription($row)
	{
        return 'Learn about the ' . $this->brandMap[$row['Brand Short Code']] . ' '
        . $this->getMappedValue('name', $row) . ' by viewing it\'s '
        . $this->getModelId($row) . ' specs and check to see if this '
        . $this->getMappedValue('name', $row) . ' model is the right model for your exact vehicle.';
	}

	protected function getMetaTitle($row)
	{
        return $this->brandMap[$row['Brand Short Code']] . ' '
        . $this->getMappedValue('name', $row) . ' '
        . $this->getModelId($row) . ' - '
        . $row['Product Category Short Code'] . ' | '
        . 'Lund International';
	}

	protected function getModelId($row)
	{
		return $row['Make'] . ' ' . $row['Model'];
	}

	protected function getDimension($row, $attribute)
	{
		$value = $row[$this->attributeMap[$attribute]['field']];
		return $value !== '0' ? $value : null;
	}

	protected function getSku($row)
	{
		return strtoupper($row['Part Number']);
	}

	protected function countryOfManufacture($attributeCode, $row)
	{
        $field = $this->attributeMap[$attributeCode]['field'];
        $value = $row[$field];

		if ($value == 0) {
           return null;
        }

        // Check cache and return if we have option id for this value
        if (isset($this->attributeMap[$attributeCode]['options'][$value])) {
            return $this->attributeMap[$attributeCode]['options'][$value];
        }

        $attributeId = $this->attributeMap[$attributeCode]['id'];

        $attribute  = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
        $allOptions = $attribute->getSource()->getAllOptions(true, true);
        foreach ($allOptions as $option) {
            if ($option['label'] == $value) {
                $this->attributeMap[$attributeCode]['options'][$value] = $option['value'];
                return $option['value'];
            }
        }

        // If we couldn't find one, we have to add it as an option
        if ($optionId = $this->addAttributeOption($attributeId, $value)) {
            return $this->attributeMap[$attributeCode]['options'][$value] = $value;
        } else {
            return null;
        }
    }


	protected function getShortDescription($row)
	{
		$shortDescription = trim($row['Short Description']);
		if ($shortDescription !== '')
			return $shortDescription;

		return '<!--empty-->';
	}

	protected function getStatus($row)
	{
		if ($row['Part Disabled'] === '0')
			return Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
		return Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
	}

	protected function getUrlKey($row)
	{
		return $this->getSku($row);
	}

	protected function getWeight($row)
	{
		$weight = trim($row['Weight']);
		if ($weight !== '')
			return $weight;

		return 0.00;
	}

	public function getOptionId($attributeCode, $row)
	{
		$field = $this->attributeMap[$attributeCode]['field'];
		$value = $row[$field];

		if ($value == 0) {
           return null;
        }

		// Check cache and return if we have option id for this value
		if (isset($this->attributeMap[$attributeCode]['options'][$value])) {
            return $this->attributeMap[$attributeCode]['options'][$value];
        }


		$attributeId = $this->attributeMap[$attributeCode]['id'];

		$attribute  = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
		$allOptions = $attribute->getSource()->getAllOptions(true, true);
		foreach ($allOptions as $option) {
			if ($option['label'] == $value) {
				$this->attributeMap[$attributeCode]['options'][$value] = $option['value'];
				return $option['value'];
			}
		}

	    // If we couldn't find one, we have to add it as an option
	    if ($optionId = $this->addAttributeOption($attributeId, $value)) {
	    	$this->attributeMap[$attributeCode]['options'][$value] = $optionId;
	    	return $optionId;
	    } else {
	    	return null;
	    }
	}

	/*
	 * This method sets the vehicle option id
	 */
	public function getVehicleTypeOptionId($value)
	{
	    $attributeCode = 'vehicle_type';

        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
        $allOptions = $attribute->getSource()->getAllOptions(true, true);

        foreach($allOptions as $option) {
            if($option['label'] == $value) {
                return $option['value'];
            }
        }
    }

	public function getOptionIdWithNA($attributeCode, $row)
	{
		$field = $this->attributeMap[$attributeCode]['field'];
		$value = $row[$field];
		if ($value === '0' || $value === '')
			$value = 'NA';

		// Check cache and return if we have option id for this value
		if (isset($this->attributeMap[$attributeCode]['options'][$value]))
			return $this->attributeMap[$attributeCode]['options'][$value];

		$attributeId = $this->attributeMap[$attributeCode]['id'];

		$attribute  = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributeCode);
		$allOptions = $attribute->getSource()->getAllOptions(true, true);
		foreach ($allOptions as $option) {
			if ($option['label'] === $value) {
				$this->attributeMap[$attributeCode]['options'][$value] = $option['value'];
				return $option['value'];
			}
		}

	    // If we couldn't find one, we have to add it as an option
	    if ($optionId = $this->addAttributeOption($attributeId, $value)) {
	    	$this->attributeMap[$attributeCode]['options'][$value] = $optionId;
	    	return $optionId;
	    } else {
	    	return null;
	    }
	}

	protected function addAttributeOption($attributeId, $optionValue)
	{
		$attributeOption = [
			'attribute_id' => $attributeId,
			'value'        => [[$optionValue]]
		];

		try {
			$this->_entity->addAttributeOption($attributeOption);
		} catch (Exception $e) {
			return false;
		}

		$fetch = 'SELECT option_id FROM eav_attribute_option_value WHERE value = "' 
				. $optionValue . '" ORDER BY value_id DESC LIMIT 1';

		$optionId = $this->_connection->fetchOne($fetch);
		return $optionId !== '' ? $optionId : false;
	}

	public function mapAttributeSetData($row, $_productData, $attributeSetData)
	{
		// Loop through attributes and map their values
		foreach ($attributeSetData['attributes'] as $attribute) {
			$value = $this->getMappedValue($attribute['code'], $row);
			if ($value !== null)
				$_productData[$attribute['code']] = $value;
		}
		return $_productData;
	}

	/* 
		[ 
			$attributeSetId => [
				'name'         => $attributeSetName,
				'attributes'   => $attributes,
				'attributeSet' => $attributeSet
			] 
		]
	*/
	public $attributeSetData   = [];

	public function getAttributeSetData($attributeSetId)
	{
		// Check cache and return if we have it
		if (isset($this->attributeSetData[$attributeSetId]))
			return $this->attributeSetData[$attributeSetId];

		// Load set by id
		$attributeSet = Mage::getModel('eav/entity_attribute_set')->load($attributeSetId);

		// Add set to cache
		$this->attributeSetData[$attributeSetId] = [
			'name'         => $attributeSet->getAttributeSetName(),
			'attributes'   => Mage::getModel('catalog/product_attribute_api')->items($attributeSetId),
			'attributeSet' => $attributeSet
		];

		// Return from cache
		return $this->attributeSetData[$attributeSetId];
	}
}