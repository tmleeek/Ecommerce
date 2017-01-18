<?php

class Unleaded_PIMS_Adminhtml_EventsController extends Mage_Adminhtml_Controller_Action 
{
    protected function _initAction() 
    {
        $this
            ->loadLayout()
            ->_setActiveMenu("unleaded_pims/pims_events")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Events"), Mage::helper("adminhtml")->__("PIMS Events"));
        return $this;
    }

    public function indexAction() 
    {
        $this->_title($this->__("PIMS Events"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_title($this->__("PIMS Events"));
        $this->_title($this->__("Edit"));

        $id    = $this->getRequest()->getParam("id");
        $model = Mage::getModel("unleaded_pims/event")->load($id);

        if ($model->getId()) {
            Mage::register("pims_data", $model);

            $this
                ->loadLayout()
                ->_setActiveMenu("unleaded_pims/events")
                ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Events"), Mage::helper("adminhtml")->__("PIMS Events"));

            $this
                ->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);

            $content = $this
                        ->getLayout()
                        ->createBlock("unleaded_pims/adminhtml_events_edit");
            $left    = $this
                        ->getLayout()
                        ->createBlock("unleaded_pims/adminhtml_events_edit_tabs");
            $this
                ->_addContent($content)
                ->_addLeft($left);

            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("unleaded_pims")->__("PIMS Event does not exist."));
            $this->_redirect("*/*/");
        }
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() 
    {
        $fileName = 'pims_events.' . date('Ymd.Hi') . '.csv';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_events_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() 
    {
        $fileName = 'pims_events.' . date('Ymd.Hi') . '.xml';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_events_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}