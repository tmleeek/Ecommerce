<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Vidtest
 * @version    1.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Vidtest_Block_Adminhtml_Video_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Prepare form before rendering HTML
     * @return AW_Referafriend_Block_Adminhtml_Rules_Edit_Form
     */
    protected function _prepareForm() {
        $id = $this->getRequest()->getParam('id');
        $params = array('id' => $this->getRequest()->getParam('id'));
        if ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY)) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_PENDING_ROUTE;
        }
        if ($this->getRequest()->getParam(AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY) == AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT) {
            $params[AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_KEY] = AW_Vidtest_Block_Adminhtml_Video_Grid::RETURN_FROM_PRODUCT;
            $params['product_id'] = $this->getRequest()->getParam('product_id');
            $params['store'] = $this->getRequest()->getParam('store');
        }

        $status = Mage::getModel('vidtest/system_config_source_video_admin_status');

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', $params),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));

        # Video group
        $fieldset = $form->addFieldset('video', array(
            'legend' => Mage::helper('vidtest')->__('Video')
                ));

        $fieldset->addType('player', 'AW_Vidtest_Model_Form_Element_Player');
        $fieldset->addType('state', 'AW_Vidtest_Model_Form_Element_State');
        $fieldset->addType('product', 'AW_Vidtest_Model_Form_Element_Product');
        $fieldset->addType('source', 'AW_Vidtest_Model_Form_Element_Source');

        # Edit video testimonial
        if ($id) {
            $fieldset->addField('api_video_url', 'player', array(
                'name' => 'api_video_url',
                'label' => Mage::helper('vidtest')->__('Movie'),
                'class' => 'required-entry',
            ));

            $fieldset->addField('state', 'state', array(
                'name' => 'state',
                'label' => Mage::helper('vidtest')->__('State on Service'),
                'class' => 'required-entry',
            ));

            # Add new video
        } else {

            $source = Mage::getModel('vidtest/system_config_source_video_sourcetype');
            $fieldset->addField('product_id', 'product', array(
                'name' => 'product_id',
                'label' => Mage::helper('vidtest')->__('Product'),
                'class' => 'required-entry',
            ));

            $fieldset->addField('source_type', 'select', array(
                'name' => 'source_type',
                'label' => Mage::helper('vidtest')->__('Source Type'),
                'values' => $source->toOptionArray(),
                'onchange' => "selectSourceType()",
            ));

            $fieldset->addField('source', 'source', array(
                'name' => 'source',
                'label' => Mage::helper('vidtest')->__('Source'),
                'required' => true,
                'note' =>   Mage::helper('vidtest')->__('Below, you can find the examples of how the embedding URL look like for each of the supported services:
                            <br /><br />Youtube - <b>http://www.youtube.com/embed/(video code)</b><span style="color: red;">*</span>
                            <br />Vimeo - <b>http://player.vimeo.com/video/(video code)</b><span style="color: red;">*</span>
                            <br />Yahoo! Screen - <b>https://screen.yahoo.com/(video code)</b><span style="color: red;">*</span>.html?format=embed
                            <br />DailyMotion - <b>http://www.dailymotion.com/embed/video/(video code)</b><span style="color: red;">*</span>
                            <br />VK - <b>https://vk.com/video_ext.php?(video code)</b><span style="color: red;">*</span>
                            <br />
                            <br /><span style="color: red;">*</span> Where (video code) should be provided in the format corresponding to the source service
                            ')
            ));
        }


        # Testimonial group
        $fieldset = $form->addFieldset('testimonial', array(
            'legend' => Mage::helper('vidtest')->__('Testimonial')
                ));

        $fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('vidtest')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('comment', 'textarea', array(
            'name' => 'comment',
            'label' => Mage::helper('vidtest')->__('Comment'),
            'required' => false,
        ));

        $fieldset->addField('author_name', 'text', array(
            'name' => 'author_name',
            'label' => Mage::helper('vidtest')->__('Author Name'),
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('author_email', 'text', array(
            'name' => 'author_email',
            'label' => Mage::helper('vidtest')->__('Author Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => Mage::helper('vidtest')->__('Status'),
            'class' => 'required-entry',
            'required' => true,
            'values' => $status->toOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            //Visibility group
            $fieldset = $form->addFieldset('visibility', array(
                'legend' => Mage::helper('vidtest')->__('Visibility')
                    ));

            $fieldset->addField('stores', 'multiselect', array(
                'label' => Mage::helper('rating')->__('Visible In'),
                'required' => true,
                'name' => 'stores[]',
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        }

        # Testimonial group
        $fieldset = $form->addFieldset('rationgs', array(
            'legend' => Mage::helper('vidtest')->__('Rating')
                ));

        $fieldset->addField('rate', 'text', array(
            'name' => 'rate',
            'label' => Mage::helper('vidtest')->__('Rate'),
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('votes', 'text', array(
            'name' => 'votes',
            'label' => Mage::helper('vidtest')->__('Votes'),
            'class' => 'required-entry',
            'required' => true,
        ));

        if (Mage::getSingleton('adminhtml/session')->getVideoData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getVideoData());
            Mage::getSingleton('adminhtml/session')->setVideoData(null);
        } elseif (Mage::registry('video_data')) {
            $form->setValues(Mage::registry('video_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}