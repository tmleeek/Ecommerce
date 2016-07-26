<?php

class Unleaded_Vehicle_Adminhtml_UlymmController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu("vehicle/ulymm")->_addBreadcrumb(Mage::helper("adminhtml")->__("Vehicle  Manager"), Mage::helper("adminhtml")->__("Vehicle Manager"));
        return $this;
    }

    public function indexAction() {
        $this->_title($this->__("Vehicle"));
        $this->_title($this->__("Manage Vehicles"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() {
        $this->_title($this->__("UL Vehicle"));
        $this->_title($this->__("Vehicle"));
        $this->_title($this->__("Edit Vehicle"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("vehicle/ulymm")->load($id);
        if ($model->getId()) {
            Mage::register("ulymm_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("vehicle/ulymm");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vehicle Manager"), Mage::helper("adminhtml")->__("Vehicle Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vehicle Description"), Mage::helper("adminhtml")->__("Vehicle Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock("vehicle/adminhtml_ulymm_edit"))->_addLeft($this->getLayout()->createBlock("vehicle/adminhtml_ulymm_edit_tabs"));
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("vehicle")->__("Vehicle does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction() {

        $this->_title($this->__("UL Vehicle"));
        $this->_title($this->__("Vehicle"));
        $this->_title($this->__("New Vehicle"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("vehicle/ulymm")->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register("ulymm_data", $model);

        $this->loadLayout();
        $this->_setActiveMenu("vehicle/ulymm");

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vehicle Manager"), Mage::helper("adminhtml")->__("Vehicle Manager"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Vehicle Description"), Mage::helper("adminhtml")->__("Vehicle Description"));


        $this->_addContent($this->getLayout()->createBlock("vehicle/adminhtml_ulymm_edit"))->_addLeft($this->getLayout()->createBlock("vehicle/adminhtml_ulymm_edit_tabs"));

        $this->renderLayout();
    }

    public function saveAction() {
        $post_data = $this->getRequest()->getPost();
        if ($post_data) {
            try {
                //save image
                try {
                    if ((bool) $post_data['image']['delete'] == 1) {
                        $post_data['image'] = '';
                    } else {
                        unset($post_data['image']);
                        if (isset($_FILES)) {
                            if ($_FILES['image']['name']) {
                                if ($this->getRequest()->getParam("id")) {
                                    $model = Mage::getModel("vehicle/ulymm")->load($this->getRequest()->getParam("id"));
                                    if ($model->getData('image')) {
                                        $io = new Varien_Io_File();
                                        $io->rm(Mage::getBaseDir('media') . DS . implode(DS, explode('/', $model->getData('image'))));
                                    }
                                }
                                $path = Mage::getBaseDir('media') . DS . 'ulvehicle' . DS . 'vehicle' . DS;
                                $uploader = new Varien_File_Uploader('image');
                                $uploader->setAllowedExtensions(array('jpg', 'png', 'gif'));
                                $uploader->setAllowRenameFiles(false);
                                $uploader->setFilesDispersion(false);
                                $destFile = $path . $_FILES['image']['name'];
                                $filename = $uploader->getNewFileName($destFile);
                                $uploader->save($path, $filename);

                                $post_data['image'] = 'ulvehicle/vehicle/' . $filename;
                            }
                        }
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
                
                $model = Mage::getModel("vehicle/ulymm")
                        ->addData($post_data)
                        ->setId($this->getRequest()->getParam("id"))
                        ->save();
                
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Vehicle was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setUlymmData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                if(strpos($e->getMessage(),'1062 Duplicate entry')){
                    Mage::getSingleton("adminhtml/session")->addError("Sorry! Please enter a unique Year, Make and Model combination to create a vehicle.");
                } else {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
                Mage::getSingleton("adminhtml/session")->setUlymmData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }
        }
        $this->_redirect("*/*/");
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam("id") > 0) {
            try {
                $model = Mage::getModel("vehicle/ulymm");
                $model->setId($this->getRequest()->getParam("id"))->delete();
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Vehicle was successfully deleted"));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massRemoveAction() {
        try {
            $ids = $this->getRequest()->getPost('ymm_ids', array());
            foreach ($ids as $id) {
                $model = Mage::getModel("vehicle/ulymm");
                $model->setId($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Vehicle(s) was successfully removed"));
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'ulvehicle.csv';
        $grid = $this->getLayout()->createBlock('vehicle/adminhtml_ulymm_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName = 'ulvehicle.xml';
        $grid = $this->getLayout()->createBlock('vehicle/adminhtml_ulymm_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}
