<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline_Edit_Form 
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

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('productline_form', [
            'legend' => Mage::helper('unleaded_productline')->__('Product Line information')
        ]);

        $fieldset->addField('name', 'text', [
            'label'              => Mage::helper('unleaded_productline')->__('Name'),
            'class'              => 'required-entry',
            'required'           => true,
            'name'               => 'name',
        ]);

        $fieldset->addField('product_line_short_code', 'text', [
            'label'              => Mage::helper('unleaded_productline')->__('Product Line Short Code'),
            'class'              => 'required-entry',
            'required'           => true,
            'name'               => 'product_line_short_code',
        ]);

        $fieldset->addField('parent_category_id', 'select', [
            'label'    => Mage::helper('unleaded_productline')->__('Parent Category'),
            'values'   => Mage::getModel('unleaded_productline/source_parentcategory')->toOptionArray(),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'parent_category_id',
        ]);

        $fieldset->addField('description', 'editor', [
            'label'     => Mage::helper('unleaded_productline')->__('Product Line Description'),
            'title'     => Mage::helper('unleaded_productline')->__('Product Line Description'),
            'style'     => 'height: 600px;',
            'wysiwyg'   => true,
            'config'    => $wysiwygConfig,
            'name'      => 'description',
        ]);

        $fieldset->addField('short_description', 'textarea', [
            'label'              => Mage::helper('unleaded_productline')->__('Short Description'),
            'name'               => 'short_description',
        ]);

        foreach ([
            'product_line_install_video' => 'Product Line Install Video',
            'product_line_v01_video'     => 'Product Line V01 Video',
            'product_line_v02_video'     => 'Product Line V02 Video',
            'product_line_v03_video'     => 'Product Line V03 Video',
            'product_line_v04_video'     => 'Product Line V04 Video',
            'product_line_v05_video'     => 'Product Line V05 Video',
            'product_line_v06_video'     => 'Product Line V06 Video',
        ] as $column => $title) {
            $fieldset->addField($column, 'text', array(
                'label'              => Mage::helper('unleaded_productline')->__($title),
                'name'               => $column,
            ));
        }

        $fieldset->addField('product_line_feature_benefits', 'editor', [
            'label'     => Mage::helper('unleaded_productline')->__('Product Line Feature Benefits'),
            'title'     => Mage::helper('unleaded_productline')->__('Product Line Feature Benefits'),
            'style'     => 'height: 600px;',
            'wysiwyg'   => true,
            'config'    => $wysiwygConfig,
            'name'      => 'product_line_feature_benefits',
        ]);
        
        if (Mage::getSingleton('adminhtml/session')->getProductlineData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getProductlineData());
            Mage::getSingleton('adminhtml/session')->setProductlineData(null);
        } else if (Mage::registry('productline_data')) {
            $form->setValues(Mage::registry('productline_data')->getData());
        }
        
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