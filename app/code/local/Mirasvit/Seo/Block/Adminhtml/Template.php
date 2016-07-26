<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.9
 * @build     1298
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Seo_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'seo';
        $this->_headerText = Mage::helper('seo')->__('SEO Template Manager');
        $this->_addButtonLabel = Mage::helper('seo')->__('Add New SEO Template');
        parent::__construct();
    }

    public function getCreateUrl ()
    {
        return $this->getUrl('*/*/add');
    }
}