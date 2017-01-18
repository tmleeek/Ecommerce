<?php

class Unleaded_PIMS_Block_Adminhtml_Imports_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('importsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() 
    {
        $collection = Mage::getModel('unleaded_pims/import')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() 
    {
        $this->addColumn('entity_id', [
            'header' => Mage::helper('unleaded_pims')->__('Entity ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'entity_id',
        ]);

        $this->addColumn('created_at', [
            'header' => Mage::helper('unleaded_pims')->__('Date'),
            'align'  => 'left',
            'width'  => '50px',
            'type'   => 'datetime',
            'index'  => 'created_at',
        ]);

        $this->addColumn('updated_at', [
            'header' => Mage::helper('unleaded_pims')->__('Last update'),
            'align'  => 'left',
            'width'  => '50px',
            'type'   => 'datetime',
            'index'  => 'updated_at',
        ]);

        $this->addColumn('imported', [
            'header'   => Mage::helper('unleaded_pims')->__('Applied?'),
            'align'    => 'left',
            'width'    => '50px',
            'type'     => 'boolean',
            'index'    => 'imported',
            'renderer' => 'unleaded_pims/adminhtml_imports_grid_imported_renderer'
        ]);

        $this->addColumn('environment', [
            'header' => Mage::helper('unleaded_pims')->__('Environment'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'text',
            'index'  => 'environment',
        ]);

        $this->addColumn('status', [
            'header'   => Mage::helper('unleaded_pims')->__('Status'),
            'align'    => 'left',
            'width'    => '150px',
            'type'     => 'text',
            'index'    => 'status',
            'renderer' => 'unleaded_pims/adminhtml_imports_grid_status_renderer'
        ]);

        $this->addColumn('file', [
            'header' => Mage::helper('unleaded_pims')->__('File'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'text',
            'index'  => 'file',
        ]);

        $this->addColumn('rollback', [
            'header' => Mage::helper('unleaded_pims')->__('Rollback'),
            'align'  => 'left',
            'width'  => '50px',
            'type'   => 'text',
            'index'  => 'rollback',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) 
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}