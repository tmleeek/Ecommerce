<?php

class Unleaded_ProductLine_Block_Adminhtml_Productline_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('productlineGrid');
        $this->setDefaultSort('ymm_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() 
    {
        $collection = Mage::getModel('unleaded_productline/productline')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() 
    {
        $this->addColumn('id', [
            'header' => Mage::helper('unleaded_productline')->__('Product Line ID'),
            'align'  => 'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'id',
        ]);

        $this->addColumn('name', [
            'header' => Mage::helper('unleaded_productline')->__('Name'),
            'index'  => 'name',
        ]);

        $this->addColumn('parent_category_id', [
            'header'  => Mage::helper('unleaded_productline')->__('Parent Category'),
            'type'    => 'options',
            'options' => Mage::getModel('unleaded_productline/source_parentcategory')->getValueLabelArray(),
            'index'   => 'parent_category_id',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) 
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _prepareMassaction() 
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_productline', [
            'label'   => Mage::helper('unleaded_productline')->__('Remove Product Line'),
            'url'     => $this->getUrl('*/adminhtml_productline/massRemove'),
            'confirm' => Mage::helper('unleaded_productline')->__('Are you sure?')
        ]);
        return $this;
    }

}
