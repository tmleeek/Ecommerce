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

	public function getMappedValue($attribute, $row)
	{
		switch ($attribute) {
			////// Standard
			case 'name';
				return $row['Product Category Code'];

			case 'description';
				return $row['Brand Product Category Long Description'];

			case 'is_active';
				return $row['Brand Product Category Disabled'] === '0' ? true : false;

			case 'is_anchor';
				return 1;
				
			case 'page_title';
				return $this->getPageTitle($row);

			case 'meta_keywords';
				return $this->getMetaKeywords($row);

			case 'meta_description';
				return $this->getMetaDescription($row);

			case 'include_in_menu';
				return 1;

			case 'display_mode';
				return 'PRODUCTS';

			case 'small_image';
				return $this->getImage($row);

			/////// Custom
			case 'short_description';
				return $row['Brand Product Category Short Description'];

			case 'url_key';
				return $this->getUrlKey($row);

			case 'product_category_short_code';
				return $row['Product Category Display Name'];

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
			default;
				return null;
		}
	}

	protected function getUrlKey($row)
	{
		return $this->slugify($row['Product Category Code']);
	}

	protected function getImage($row)
	{
		$value = $row['Product Category Media Asset'];

		// First check to see if we have data
		if ($value === '')
			return null;

		// First copy image to local dir
		if (!$localPath = Mage::helper('unleaded_pims/ftp')->getCategoryImage($value)){
            return $this->error('Unable to download image');
        }

		return $value;
	}

	protected function getMetaDescription($row)
	{
		return 'View all of our current ' . $this->getMappedValue('name', $row) . ' products for vehicles that are available for purchase today in-store nationally or online.';
	}

	protected function getMetaKeywords($row)
	{
		return null;
	}

	protected function getPageTitle($row)
	{
		return $this->getMappedValue('name', $row) . ' | Lund International';
	}
}