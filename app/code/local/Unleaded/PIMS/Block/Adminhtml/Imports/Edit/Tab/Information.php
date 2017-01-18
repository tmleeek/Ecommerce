<?php

class Unleaded_PIMS_Block_Adminhtml_Imports_Edit_Tab_Information 
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $layout = $this->getLayout();

        $form = new Varien_Data_Form([
            'id'      => 'tab_form',
            'action'  => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $informationFieldset = $form->addFieldset('tab_form_fieldset', [
            'legend' => Mage::helper('unleaded_pims')->__('Import Information')
        ]);

        $informationFieldset->addField('entity_id', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Entity Id'),
            'readonly' => true,
            'name'     => 'entity_id',
        ]);

        $informationFieldset->addField('imported', 'select', [
            'label'    => Mage::helper('unleaded_pims')->__('Has this import been applied?'),
            'readonly' => true,
            'name'     => 'imported',
            'values'   => [
                [
                    'value' => 0,
                    'label' => 'No'
                ],
                [
                    'value' => 1,
                    'label' => 'Yes'
                ]
            ],
            'disabled' => true
        ]);

        $informationFieldset->addField('environment', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Environment'),
            'readonly' => true,
            'name'     => 'environment',
        ]);

        $informationFieldset->addField('created_at', 'datetime', [
            'label'    => Mage::helper('unleaded_pims')->__('Date'),
            'readonly' => true,
            'name'     => 'created_at',
            'format'   => 'Y-M-d H:m:s'
        ]);

        $informationFieldset->addField('updated_at', 'datetime', [
            'label'    => Mage::helper('unleaded_pims')->__('Last Update'),
            'readonly' => true,
            'name'     => 'updated_at',
            'format'   => 'Y-M-d H:m:s'
        ]);

        $informationFieldset->addField('status', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Status'),
            'readonly' => true,
            'name'     => 'status',
        ]);

        $informationFieldset->addField('file', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('CSV File from Lund'),
            'readonly' => true,
            'name'     => 'file',
        ]);

        $informationFieldset->addField('rollback', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Database rollback file'),
            'readonly' => true,
            'name'     => 'rollback',
        ]);

        $informationFieldset->addField('operation', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Import Operation (full, add, delete, etc...)'),
            'readonly' => true,
            'name'     => 'operation',
        ]);

        $informationFieldset->addField('store_code', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Store Code'),
            'readonly' => true,
            'name'     => 'store_code',
        ]);

        $informationFieldset->addField('import_type', 'text', [
            'label'    => Mage::helper('unleaded_pims')->__('Import Type (parts or brands)'),
            'readonly' => true,
            'name'     => 'import_type',
        ]);

        if (Mage::helper('unleaded_pims')->isNotProduction()) {
            $buttonData = [
                'label'      => Mage::helper('unleaded_pims')->__('Import this file to ' . Mage::helper('unleaded_pims')->getEnvironment()),
                'onclick'    => 'confirmImport();',
                'class'      => 'save',
                'after_html' => $layout
                                    ->createBlock('core/template')
                                    ->setTemplate('unleaded_pims/import/confirm.phtml')
                                    ->setData(['import_id' => $this->getRequest()->getParam('id')])
                                    ->toHtml()
            ];

            $importButton = $layout
                                ->createBlock('adminhtml/widget_button')
                                ->setData($buttonData)
                                ->setName('import_action');

            $informationFieldset->setHeaderBar($importButton->toHtml());
        }
        
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

    public function getTabLabel()
    {
        return Mage::helper('unleaded_pims')->__('Import Information');
    }

    public function getTabTitle()
    {
        return Mage::helper('unleaded_pims')->__('Import Information');
    }

    public function canShowTab()
    {
        return true;   
    }

    public function isHidden()
    {
        return false;
    }
}