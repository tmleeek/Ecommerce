<?php
class Unleaded_PIMS_Block_Adminhtml_Imports_Grid_Status_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());
		if ($value === 'error')
			return $value . $this->getErrorScript();
		return $value;
	}

	protected function getErrorScript()
	{
		return 
			'<script class="error-script" type="text/javascript">
				(function() {
					var tr = $$(".error-script")[0].parentElement.parentElement;
					tr.classList = tr.classList + " error";
				}());
			</script>';
	}
}