<?php

class Unleaded_Vehicle_Block_Adminhtml_Ulymm_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId("ulymmGrid");
        $this->setDefaultSort("ymm_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("vehicle/ulymm")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("ymm_id", array(
            "header" => Mage::helper("vehicle")->__("Vehicle ID"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "ymm_id",
        ));

        $this->addColumn("year", array(
            "header" => Mage::helper("vehicle")->__("Vehicle Year"),
            "index" => "year",
        ));
        $this->addColumn("make", array(
            "header" => Mage::helper("vehicle")->__("Vehicle Make"),
            "index" => "make",
        ));
        $this->addColumn("model", array(
            "header" => Mage::helper("vehicle")->__("Vehicle Model"),
            "index" => "model",
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('ymm_id');
        $this->getMassactionBlock()->setFormFieldName('ymm_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_ulymm', array(
            'label' => Mage::helper('vehicle')->__('Remove Vehicle'),
            'url' => $this->getUrl('*/adminhtml_ulymm/massRemove'),
            'confirm' => Mage::helper('vehicle')->__('Are you sure?')
        ));
        return $this;
    }

}
