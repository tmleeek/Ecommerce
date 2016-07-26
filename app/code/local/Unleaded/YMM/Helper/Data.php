<?php

class Unleaded_YMM_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $segmentCache = false;

	public function getVehicleFromSegment($vehicleSegment)
	{
		$vehicle = [];

		Mage::log($vehicleSegment);

		// Vehicle make and model can have - and /
		if (!preg_match('/([0-9]{4})-([a-zA-Z0-9_\-\/]*)-([a-zA-Z0-9_\-\/]*)/', $vehicleSegment, $match))
			return $vehicle;

		$vehicle['year']  = str_replace('_', ' ', $match[1]);
		$vehicle['make']  = str_replace('_', ' ', $match[2]);
		$vehicle['model'] = str_replace('_', ' ', $match[3]);

		return $vehicle;
	}

	public function getVehicleIdsFromSegment($vehicleSegment)
	{
		$vehicle = $this->getVehicleFromSegment($vehicleSegment);

		Mage::log($vehicle['year']);
		Mage::log($vehicle['make']);
		Mage::log($vehicle['model']);

		return $this->getVehicleIds($vehicle);
	}

	public function getVehicleIds($vehicle)
	{
		$vehicleCollection = Mage::getModel('vehicle/ulymm')
							->getCollection()
							->addFieldToFilter('year', $vehicle['year'])
							->addFieldToFilter('make', $vehicle['make'])
							->addFieldToFilter('model', $vehicle['model']);

		$vehicleIds = [];
		foreach ($vehicleCollection as $_vehicle)
			$vehicleIds[] = $_vehicle->getId();

		return $vehicleIds;
	}

	public function getProductResource()
	{
		if (!$this->productResource)
			$this->productResource = Mage::getResourceModel('catalog/product');
		return $this->productResource;
	}

	public function getVehicleUrl($year, $make, $model)
	{
		$url = Mage::getBaseUrl() . 'models/';
        return $url . $this->getVehicleSegment($year, $make, $model);
	}

	public function getVehicleSegment($year, $make, $model)
	{
		$key = $year . $make . $model;
		if (isset($this->segmentCache[$key]))
			return $this->segmentCache[$key];

		$vehiclePieces = [];
        foreach (['year', 'make', 'model'] as $variable)
            $vehiclePieces[] = strtolower(str_replace(' ', '_', $$variable));

        $segment .= implode('-', $vehiclePieces);

        $this->segmentCache[$key] = $segment;
        return $this->segmentCache[$key];
	}

	public function getProductUrl($year, $make, $model, $targetCategoryId)
	{
		// First find this product
		$vehicleIds = $this->getVehicleIds([
			'year'  => $year,
			'make'  => $make,
			'model' => $model
		]);

		// Now we find the product
		$where = [];
		foreach ($vehicleIds as $id)
			$where[] = ['attribute' => 'compatible_vehicles', 'finset' => $id];

		$productCollection = Mage::getModel('catalog/category')->load($targetCategoryId)
								->getProductCollection()
								->addAttributeToSelect('compatible_vehicles')
								->addAttributeToFilter('compatible_vehicles', $where);
		
		foreach ($productCollection as $product) {
			var_dump($product->getId());
		}
		var_dump($productCollection->getSize());exit;
	}
}