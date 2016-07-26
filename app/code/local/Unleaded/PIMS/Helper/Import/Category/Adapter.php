<?php

class Unleaded_PIMS_Helper_Import_Category_Adapter 
	extends Unleaded_PIMS_Helper_Data
{
	protected $categoryResource;

	private $_entity;
	private $_connection;

	public function __construct()
	{
		$this->categoryResource = Mage::getResourceSingleton('catalog/category');
		$this->_entity          = new Mage_Eav_Model_Entity_Setup('core_setup');
		$this->_connection      = $this->_entity->getConnection('core_read');
	}

	public function getMappedValue($attribute, $row, $child = true)
	{
		switch ($attribute) {
			////// Standard
			case 'name';
				if ($child)
					return $row['Product Line Display Name'];
				return $row['Product Category Code'];

			case 'description';
				if ($child)
					return $row['Product Line HTML'];
				return $row['Brand Product Category Long Description'];

			case 'is_active';
				if ($child)
					return $row['Product Line Disabled'] === '0' ? true : false;
				return $row['Brand Product Category Disabled'] === '0' ? true : false;

			case 'is_anchor';
				return 1;
				
			case 'page_title';
				return $this->getPageTitle($row, $child);

			case 'meta_keywords';
				return $this->getMetaKeywords($row, $child);

			case 'meta_description';
				return $this->getMetaDescription($row, $child);

			case 'include_in_menu';
				if ($child);
					return 0;
				return 1;

			case 'display_mode';
				return 'PRODUCTS';

			case 'small_image';
				return $this->getImage($row);

			/////// Custom
			case 'short_description';
				if ($child)
					return $row['Product Line Description'];
				return $row['Brand Product Category Short Description'];

			case 'product_category_display_name';
				if ($child)
					return $row['Product Line Display Name'];
				return $row['Product Category Display Name'];
			
			case 'product_line_features';
				return $this->getProductLineFeatures($row);

			case 'product_line_install_video';
				return $row['Product Line Install Video'];

			case 'product_line_v01_video';
			case 'product_line_v02_video';
			case 'product_line_v03_video';
			case 'product_line_v04_video';
			case 'product_line_v05_video';
			case 'product_line_v06_video';
				$field = str_replace('Video', '- video', ucwords(str_replace('_', ' ', $attribute)));
				return $row[$field];

			// case 'featured_image';
				// return $this->getFeaturedImage($row);
			// case 'thumbnail';
				// return $this->getThumbnail($row);

			// Unassigned custom
			case 'is_featured_category';
			case 'short_description_featured';
			case 'featured_title';
			case 'page_header';			

			// Unassigned standard
			case 'url_key';
			default;
				return null;
		}
	}

	protected function getProductLineFeatures($row)
	{
		$features = '<ul><li>';
		for ($i = 1; $i <= 20; $i++)
			$features .= $row['Product Line Feature - Benefits ' . $i] . '</li><li>';

		$features = str_replace('<li></li>', '', substr($features, 0, -4)) . '</ul>';
		return $features;
	}

	protected function getImage($row)
	{
		$value = $row['Product Category Media Asset'];

		// First check to see if we have data
		if ($value === '')
			return null;

		// First copy image to local dir
		if (!$localPath = Mage::helper('unleaded_pims/ftp')->getCategoryImage($value))
			return $this->error('Unable to download image');

		return $value;
	}

	protected function getMetaDescription($row, $child)
	{
		return 'View all of our current ' . $this->getMappedValue('name', $row, $child) . ' products for vehicles that are available for purchase today in-store nationally or online.';
	}

	protected function getMetaKeywords($row, $child)
	{
		return null;
	}

	protected function getPageTitle($row, $child)
	{
		return $this->getMappedValue('name', $row, $child) . ' | Lund International';
	}
}