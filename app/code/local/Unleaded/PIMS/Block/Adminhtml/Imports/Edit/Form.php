<?php

class Unleaded_PIMS_Block_Adminhtml_Imports_Edit_Form 
    extends Mage_Adminhtml_Block_Widget_Form 
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('adminhtml_imports_form');
        $this->setTitle(Mage::helper('unleaded_pims')->__('Import Information'));
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
			'id'     => 'edit_form',
			'action' => 'save',
			'method' => 'post'
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}