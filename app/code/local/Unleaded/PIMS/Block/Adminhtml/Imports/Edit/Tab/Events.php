<?php

class Unleaded_PIMS_Block_Adminhtml_Imports_Edit_Tab_Events 
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('eventsGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() 
    {
        if (!$model = Mage::registry('pims_data'))
            return;

        $collection = $model->getEvents();

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

        $this->addColumn('event_name', [
            'header' => Mage::helper('unleaded_pims')->__('Event'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'text',
            'index'  => 'event_name',
        ]);

        $this->addColumn('initiator', [
            'header' => Mage::helper('unleaded_pims')->__('Initiator'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'text',
            'index'  => 'initiator',
        ]);

        $this->addColumn('initiator_type', [
            'header' => Mage::helper('unleaded_pims')->__('Initiator Type'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'text',
            'index'  => 'initiator_type',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) 
    {
        return $this->getUrl('*/adminhtml_events/edit', ['id' => $row->getId()]);
    }

    public function getTabLabel()
    {
        return Mage::helper('unleaded_pims')->__('Import Events');
    }

    public function getTabTitle()
    {
        return Mage::helper('unleaded_pims')->__('Import Events');
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