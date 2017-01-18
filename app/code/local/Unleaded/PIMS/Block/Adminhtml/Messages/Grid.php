<?php

class Unleaded_PIMS_Block_Adminhtml_Messages_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid 
{
    public function __construct() 
    {
        parent::__construct();
        $this->setId('messagesGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() 
    {
        $collection = Mage::getModel('unleaded_pims/message')->getCollection();
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
            'header' => Mage::helper('unleaded_pims')->__('Last Update'),
            'align'  => 'left',
            'width'  => '50px',
            'type'   => 'datetime',
            'index'  => 'updated_at',
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

        $this->addColumn('body', [
            'header' => Mage::helper('unleaded_pims')->__('Message Body'),
            'align'  => 'left',
            'width'  => '150px',
            'type'   => 'longtext',
            'index'  => 'body',
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