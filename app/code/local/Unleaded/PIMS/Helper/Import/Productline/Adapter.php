<?php

class Unleaded_PIMS_Helper_Import_ProductLine_Adapter 
	extends Unleaded_PIMS_Helper_Data
{
	public function getMappedValue($attribute, $row)
	{
		switch ($attribute) {
			case 'name';
				return $row['Product Line Display Name'];

			case 'description';
				return $row['Product Line HTML'];

			case 'short_description';
				return $row['Product Line Description'];

			case 'product_line_short_code';
				return $row['Product Line Short Code'];
			
			case 'product_line_feature_benefits';
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

		// Make sure we aren't sending an empty list
		if ($features === '<ul></ul>')
			return null;
		
		return $features;
	}
}