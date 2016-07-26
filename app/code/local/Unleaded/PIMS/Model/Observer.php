<?php

class Unleaded_PIMS_Model_Observer
{
	public function addViewProductButton($observer)
    {
		$_block = $observer->getBlock();
		$_type  = $_block->getType();
        if ($_type == 'adminhtml/catalog_product_edit') {
        	if (!$_deleteButton = $_block->getChild('delete_button'))
        		return;
            $_block->setChild('product_view_button',
                $_block->getLayout()->createBlock('unleaded_pims/adminhtml_widget_viewproductbutton')
            );
            $_deleteButton->setBeforeHtml(
            	$_block->getChild('product_view_button')->toHtml()
            );
        }
    }
}