<?php

class Unleaded_PIMS_Block_Adminhtml_Messages_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    public function __construct() 
    {
        parent::__construct();
        
        $this->_objectId   = 'entity_id';
        $this->_blockGroup = 'unleaded_pims';
        $this->_controller = 'adminhtml_messages';
        $this->_mode       = 'edit';

        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getHeaderText() 
    {
        if (Mage::registry('pims_data') && Mage::registry('pims_data')->getId()) {
            return Mage::helper('unleaded_pims')->__('View Message');
        }
    }
}