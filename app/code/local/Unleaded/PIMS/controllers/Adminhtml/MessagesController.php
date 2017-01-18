<?php

class Unleaded_PIMS_Adminhtml_MessagesController extends Mage_Adminhtml_Controller_Action 
{
    protected function _initAction() 
    {
        $this
            ->loadLayout()
            ->_setActiveMenu("unleaded_pims/pims_messages")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Messages"), Mage::helper("adminhtml")->__("PIMS Messages"));
        return $this;
    }

    public function indexAction() 
    {
        $this->_title($this->__("PIMS Messages"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_title($this->__("PIMS Messages"));
        $this->_title($this->__("Edit"));

        $id    = $this->getRequest()->getParam("id");
        $model = Mage::getModel("unleaded_pims/message")->load($id);

        if ($model->getId()) {
            Mage::register("pims_data", $model);

            $this
                ->loadLayout()
                ->_setActiveMenu("unleaded_pims/messages")
                ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Messages"), Mage::helper("adminhtml")->__("PIMS Messages"));

            $this
                ->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);

            $content = $this
                        ->getLayout()
                        ->createBlock("unleaded_pims/adminhtml_messages_edit");
            $left    = $this
                        ->getLayout()
                        ->createBlock("unleaded_pims/adminhtml_messages_edit_tabs");
            $this
                ->_addContent($content)
                ->_addLeft($left);

            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("unleaded_pims")->__("PIMS Message does not exist."));
            $this->_redirect("*/*/");
        }
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() 
    {
        $fileName = 'pims_messages.' . date('Ymd.Hi') . '.csv';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_messages_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() 
    {
        $fileName = 'pims_messages.' . date('Ymd.Hi') . '.xml';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_messages_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}