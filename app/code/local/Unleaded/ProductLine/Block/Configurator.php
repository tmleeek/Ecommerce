<?php

class Unleaded_ProductLine_Block_Configurator extends Mage_Core_Block_Template
{
	public $attributeOrder = [];

	public $vehicleOptionCount;
	public $vehicleOptionAttributes = [
		'year',
		'make',
		'model',
		'sub_model',
		'sub_detail'
	];

	public $productOptionCount;
	public $productOptionAttributes = [
		'bed_length',
		'bed_type',
		'flare_height',
		'flare_tire_coverage',
		'box_style',
		'box_opening_type',
		'color',
		'finish',
		'style',
		'material',
		'material_thickness',
		'sold_as',
		'tube_shape',
		'tube_size',
		'liquid_storage_capacity'
	];

	public $optionLabels = [];

	private $lastDepth = false;

	public function __construct()
	{
		$this->vehicleOptionCount = count($this->vehicleOptionAttributes);
		$this->productOptionCount = count($this->productOptionAttributes);

		// Initialize attribute order
		$this->attributeOrder = array_merge($this->vehicleOptionAttributes, $this->productOptionAttributes);
	}

	public function printOptions()
	{
		foreach ($this->attributeOrder as $attributeCode) {
		?>
			<div class="select-field-options">
				<label><?php echo $this->unslug($attributeCode); ?></label>
				<div class="select-style">
					<select name="<?php echo $attributeCode; ?>">
						<option value="false"></option>
					</select>
				</div>
			</div>
		<?php
		}
	}

	public function printVehicleAttributes()
	{
		echo '[';
		foreach ($this->vehicleOptionAttributes as $attributeCode) {
			echo '"' . $attributeCode . '",';
		}
		echo ']';
	}

	private function unslug($text)
	{
		return str_replace('_', ' ', ucwords($text));
	}

	public function printAttributeOrder()
	{
		return json_encode($this->attributeOrder);
	}

	public function printInitAttributes()
	{
		echo '{';
		foreach ($this->attributeOrder as $attributeCode) {
			echo $attributeCode . ': $(\'select[name="' . $attributeCode . '"]\'),';
		}
		echo '}';
	}

	public function printCurrentVehicle()
	{	
		$cookie = Mage::getSingleton('core/cookie');
		if (!$segment = $cookie->get('currentVehicle')) {
			echo 'false';
			return;
		}

		$vehicle = Mage::helper('unleaded_ymm')->getVehicleFromSegment($segment);
		echo '{'
				. 'year: "' . $vehicle['year'] . '",'
				. 'make: "' . $vehicle['make'] . '",'
				. 'model: "' . $vehicle['model'] . '"'
			. '}';
	}

	public function printOptionLabels()
	{
		echo '{';
		foreach ($this->optionLabels as $attributeCode => $optionValues) {
			echo $attributeCode . ': {';
			foreach ($optionValues as $value => $label) {
				echo $value . ': "' . $label . '",';
			}
			echo '},';
		}
		echo '}';
	}

	public function initOptionLabels($productLine)
	{
		$productTree = json_decode($productLine->getProductTree());

		foreach ($this->productOptionAttributes as $attributeCode) {
			$this->optionLabels[$attributeCode] = [];
		}

		$this->recursiveOptionLabels(1, $productTree);
	}

	private function recursiveOptionLabels($depth, $currentTree)
	{
		if ($this->isBeyondLastDepth($depth))
			return;

		// // Mage::log(print_r($currentTree, true));
		// Mage::log('Getting option labels for depth: ' . $depth);
		// We need to figure out which attribute this is based on it's depth in the tree
		if ($depth <= $this->vehicleOptionCount)
			$attributeCode = $this->vehicleOptionAttributes[$depth - 1];
		else
			$attributeCode = $this->productOptionAttributes[$depth - 1 - $this->vehicleOptionCount];
		// Mage::log('-- This level attribute code: ' . $attributeCode);

		// If the depth is less than the vehicle options array, we need to continue
		// because the vehicle options are already printed as labels.
		foreach ($currentTree as $optionValue => $nextLevelOfTree) {
			// Mage::log('-- This level option value: ' . $optionValue);
			if ($depth <= $this->vehicleOptionCount) {
				// Mage::log('!---- This is a vehicle attribute, continuing');
				// These are 'year', 'make', 'model', 'sub_model', or 'sub_detail'
				// No need to grab option labels, just go into the next level
				$this->recursiveOptionLabels(($depth + 1), $nextLevelOfTree);
				continue;
			}
			// if ($depth >= $this->productOptionCount) {
			// 	// Mage::log('!---- This is beyond the product option count, continuing');
			// 	continue;
			// }
			// This is an option value that we need a label for, but only if it is not empty
			if ($optionValue === '_empty_') {
				// Mage::log('!---- This option value is empty, continuing');
				$this->recursiveOptionLabels(($depth + 1), $nextLevelOfTree);
				continue;
			}
			// Make sure we don't already have this option label
			if (isset($this->optionLabels[$attributeCode][$optionValue])) {
				// Mage::log('!---- Already have this value, continuing');
				// We already have this, continue
				$this->recursiveOptionLabels(($depth + 1), $nextLevelOfTree);
				continue;
			}
			// Mage::log('-- We need to get this value');

			// OK we ACTUALLY need this option label now
			$this->optionLabels[$attributeCode][$optionValue] = $this->getOptionLabel($attributeCode, $optionValue);
			// Mage::log('---- Got value: ' . $this->optionLabels[$attributeCode][$optionValue]);

			// And we need to keep recursing
			// Mage::log('---- Recursing into next level');
			$this->recursiveOptionLabels(($depth + 1), $nextLevelOfTree);
		}
	}

	private function getOptionLabel($attributeCode, $optionValue)
	{
		// Mage::log('---- Trying to get value: ' . $optionValue . ' for attribute: ' . $attributeCode);

		// We are not actually loading a product, just creating an instance
		$product = Mage::getModel('catalog/product')
	                ->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)
	                ->setData($attributeCode, $optionValue);
		return $product->getAttributeText($attributeCode);
	}

	private function isBeyondLastDepth($depth)
	{
		if ($this->lastDepth)
			return $this->lastDepth < $depth;

		$this->lastDepth = $this->vehicleOptionCount + $this->productOptionCount;
		return $this->lastDepth < $depth;
	}
}