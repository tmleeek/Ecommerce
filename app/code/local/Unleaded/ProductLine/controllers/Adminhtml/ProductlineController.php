<?php

class Unleaded_ProductLine_Adminhtml_ProductlineController extends Mage_Adminhtml_Controller_Action 
{
    protected function _initAction() 
    {
        $this
            ->loadLayout()
            ->_setActiveMenu("unleaded_productline/productline")
            ->_addBreadcrumb(Mage::helper("adminhtml")->__("Product Line Manager"), Mage::helper("adminhtml")->__("Product Line Manager"));
        return $this;
    }

    public function indexAction() 
    {
        $this->_title($this->__("Product Line"));
        $this->_title($this->__("Manage"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction() 
    {
        $this->_title($this->__("Product Line"));
        $this->_title($this->__("Manage"));
        $this->_title($this->__("Edit"));

        $id    = $this->getRequest()->getParam("id");
        $model = Mage::getModel("unleaded_productline/productline")->load($id);

        if ($model->getId()) {
            Mage::register("productline_data", $model);

            $this
                ->loadLayout()
                ->_setActiveMenu("unleaded_productline/productline")
                ->_addBreadcrumb(Mage::helper("adminhtml")->__("Product Line Manager"), Mage::helper("adminhtml")->__("Product Line Manager"));

            $this
                ->getLayout()
                ->getBlock("head")
                ->setCanLoadExtJs(true);

            $content = $this
                        ->getLayout()
                        ->createBlock("unleaded_productline/adminhtml_productline_edit");
            $left    = $this
                        ->getLayout()
                        ->createBlock("unleaded_productline/adminhtml_productline_edit_tabs");
            $this
                ->_addContent($content)
                ->_addLeft($left);

            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("unleaded_productline")->__("Product Line does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction() 
    {
        $this->_title($this->__("Product Line"));
        $this->_title($this->__("Manage"));
        $this->_title($this->__("New"));

        $id    = $this->getRequest()->getParam("id");
        $model = Mage::getModel("unleaded_productline/productline")->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register("productline_data", $model);

        $this
            ->loadLayout()
            ->_setActiveMenu("unleaded_productline/productline");

        $this
            ->getLayout()
            ->getBlock("head")
            ->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Product Line Manager"), Mage::helper("adminhtml")->__("Product Line Manager"));

        $content = $this
                    ->getLayout()
                    ->createBlock("unleaded_productline/adminhtml_productline_edit");
        $left    = $this
                    ->getLayout()
                    ->createBlock("unleaded_productline/adminhtml_productline_edit_tabs");
        $this
            ->_addContent($content)
            ->_addLeft($left);

        $this->renderLayout();
    }

    public function saveAction() 
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {                
                $model = Mage::getModel("unleaded_productline/productline")
                        ->addData($postData)
                        ->setId($this->getRequest()->getParam("id"))
                        ->save();
                
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Product Line was successfully saved"));
                Mage::getSingleton("adminhtml/session")->setProductlineData(false);

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                if(strpos($e->getMessage(),'1062 Duplicate entry')){
                    Mage::getSingleton("adminhtml/session")->addError("Duplicate Entry error");
                } else {
                    Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                }
                Mage::getSingleton("adminhtml/session")->setProductlineData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }
        }
        $this->_redirect("*/*/");
    }

    public function deleteAction() 
    {
        if ($this->getRequest()->getParam("id") > 0) {
            try {
                $model = Mage::getModel("unleaded_productline/productline");
                $model
                    ->setId($this->getRequest()->getParam("id"))
                    ->delete();

                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Product Line was successfully deleted"));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massRemoveAction() 
    {
        try {
            $ids = $this->getRequest()->getPost('ymm_ids', array());
            foreach ($ids as $id) {
                $model = Mage::getModel("unleaded_productline/productline");
                $model->setId($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Product Line(s) was/were successfully removed"));
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() 
    {
        $fileName = 'productline.csv';
        $grid = $this->getLayout()->createBlock('unleaded_productline/adminhtml_productline_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() 
    {
        $fileName = 'productline.xml';
        $grid = $this->getLayout()->createBlock('unleaded_productline/adminhtml_productline_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}
