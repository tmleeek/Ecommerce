<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline_Edit_Tab_Form 
    extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm() 
    {
        $form          = new Varien_Data_Form();
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}