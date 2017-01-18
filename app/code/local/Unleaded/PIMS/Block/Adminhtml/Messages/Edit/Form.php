<?php

class Unleaded_PIMS_Block_Adminhtml_Messages_Edit_Form 
    extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            "id"      => "edit_form",
            "action"  => $this->getUrl("*/*/save", ["id" => $this->getRequest()->getParam("id")]),
            "method"  => "post",
            "enctype" => "multipart/form-data",
        ]);

        $fieldset = $form->addFieldset('edit_form', [
            'legend' => Mage::helper('unleaded_pims')->__('PIMS information')
        ]);



        $fieldset->addField('entity_id', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Entity Id'),
            'readonly' => true,
            'name'     => 'entity_id',
        ]);

        $fieldset->addField('created_at', 'datetime', [
            'label'    => Mage::helper('unleaded_pims')->__('Date'),
            'readonly' => true,
            'name'     => 'created_at',
            'format'   => 'Y-M-d H:m:s'
        ]);

        $fieldset->addField('updated_at', 'datetime', [
            'label'    => Mage::helper('unleaded_pims')->__('Last Update'),
            'readonly' => true,
            'name'     => 'updated_at',
            'format'   => 'Y-M-d H:m:s'
        ]);

        $fieldset->addField('initiator_type', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Inititator Type'),
            'readonly' => true,
            'name'     => 'initiator_type',
        ]);

        $fieldset->addField('initiator', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Inititator'),
            'readonly' => true,
            'name'     => 'initiator',
        ]);
        
        $fieldset->addField('body', 'textarea', [
            'label'    => Mage::helper('unleaded_pims')->__('Message Body'),
            'readonly' => true,
            'name'     => 'body',
        ]);

        if (Mage::getSingleton('adminhtml/session')->getPimsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPimsData());
            Mage::getSingleton('adminhtml/session')->setPimsData(null);
        } else if (Mage::registry('pims_data')) {
            $form->setValues(Mage::registry('pims_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}