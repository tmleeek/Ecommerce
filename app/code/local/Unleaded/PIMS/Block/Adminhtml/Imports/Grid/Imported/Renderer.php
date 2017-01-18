<?php
class Unleaded_PIMS_Block_Adminhtml_Imports_Grid_Imported_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		return $value == 0 ? 'No' : 'Yes';
	}
}