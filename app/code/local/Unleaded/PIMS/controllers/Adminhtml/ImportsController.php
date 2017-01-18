<?php

class Unleaded_PIMS_Adminhtml_ImportsController extends Mage_Adminhtml_Controller_Action 
{
    protected $import;

    protected function _initAction()
    {
        $this
            ->loadLayout()
            ->_setActiveMenu("unleaded_pims/pims_imports")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Imports"), Mage::helper("adminhtml")->__("PIMS Imports"));
        return $this;
    }

    public function indexAction() 
    {
        $this->_title($this->__("PIMS Imports"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_title($this->__("PIMS Imports"));
        $this->_title($this->__("Edit"));

        $id    = $this->getRequest()->getParam("id");
        $model = Mage::getModel("unleaded_pims/import")->load($id);

        if ($model->getId()) {
            Mage::register("pims_data", $model);

            $this
                ->loadLayout()
                ->_setActiveMenu("unleaded_pims/imports")
                ->_addBreadcrumb(Mage::helper("adminhtml")->__("PIMS Imports"), Mage::helper("adminhtml")->__("PIMS Imports"));

            $this
                ->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);

            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("unleaded_pims")->__("PIMS Import does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function importAction()
    {
        $this->import = Mage::getModel('unleaded_pims/import')->load($this->getRequest()->getParam('id'));
        $this->_doImport($this->import->getId());

        Mage::getSingleton("adminhtml/session")->addNotice(Mage::helper("unleaded_pims")->__("A database rollback has been created and the import process will begin with the next cron check. You will be notified when the import is ready for review."));
        
        $this->_redirect("*/*/");
    }

    private function _doImport($importId)
    {
        // Create new event, attach message and attach it to current import
        $event = Mage::helper('unleaded_pims')->newSystemEventWithMessage('Import Procedure Started', Unleaded_PIMS_Model_Message::TYPE_ACTION);
        $event->setEventName(Unleaded_PIMS_Model_Event::IMPORT_QUEUE)->save();
        $this->import->attachEvent($event);

        // Get the rollback file name with absolute directory path and note it in the event
        $rollbackFile = Mage::helper('unleaded_pims')->getRollbackFilename();
        $event->attachNewSystemMessage('Dumping rollback to ' . $rollbackFile['fullPath']);

        // Get the dump command
        $command = $this->_getDumpCommand($rollbackFile['fullPath']);

        // Execute the sql dump
        $output = $return = null;
        exec($command, $output, $return);
        if ($return !== 0) {
            // There was an error, the return will be the error code
            $event->attachNewSystemMessage('Error occurred while dumping database: ' . var_export($return, true), Unleaded_PIMS_Model_Message::TYPE_ERROR);
            $this->import
                    ->setStatus(Unleaded_PIMS_Model_Import::STATUS_ERROR)
                    ->setUpdatedAt(0)
                    ->save();
            return;
        }

        // Rollback dump was successful, save filename to import
        $this->import
                ->setRollback($rollbackFile['fileName'])
                ->setStatus(Unleaded_PIMS_Model_Import::STATUS_CRON_READY)
                ->setUpdatedAt(0)
                ->save();
    }

    private function _getDumpCommand($rollbackFile)
    {
        $config    = Mage::getConfig()->getResourceConnectionConfig("default_setup");
        $mysqldump = '/usr/local/bin/mysqldump';

        return $mysqldump . ' --user=' . $config->username . ' --password=' . $config->password
                . ' --host=' . $config->host . ' ' . $config->dbname . ' > ' . $rollbackFile;
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() 
    {
        $fileName = 'pims_imports.' . date('Ymd.Hi') . '.csv';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_imports_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() 
    {
        $fileName = 'pims_imports.' . date('Ymd.Hi') . '.xml';
        $grid = $this->getLayout()->createBlock('unleaded_pims/adminhtml_imports_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}