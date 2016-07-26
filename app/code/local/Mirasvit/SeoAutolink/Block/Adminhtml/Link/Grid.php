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



class Mirasvit_SeoAutolink_Block_Adminhtml_Link_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('grid');
        $this->setDefaultSort('link_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('seoautolink/link')
            ->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('link_id', array(
                'header' => Mage::helper('seoautolink')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'link_id',
            )
        );

        $this->addColumn('keyword', array(
                'header' => Mage::helper('seoautolink')->__('Keyword'),
                'align' => 'left',
                'index' => 'keyword',
            )
        );

        $this->addColumn('url', array(
                'header' => Mage::helper('seoautolink')->__('URL'),
                'align' => 'left',
                'index' => 'url',
            )
        );

        $this->addColumn('sort_order', array(
                'header' => Mage::helper('seoautolink')->__('Sort order'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'sort_order',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('seoautolink')->__('Store View'),
                'align' => 'left',
                'width' => '200px',
                'index' => 'link_id',
                'renderer' => 'seoautolink/adminhtml_widget_renderer_store',
                'type' => 'store',
                'store_all' => true,
                'store_view' => true,
                'sortable' => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_active', array(
            'header' => Mage::helper('seoautolink')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('seoautolink')->__('Enabled'),
                0 => Mage::helper('seoautolink')->__('Disabled'),
            ),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('link_id');
        $this->getMassactionBlock()->setFormFieldName('link_id');

        $this->getMassactionBlock()->addItem('enable', array(
            'label' => Mage::helper('seoautolink')->__('Enable'),
            'url' => $this->getUrl('*/*/massEnable'),
        ));

        $this->getMassactionBlock()->addItem('disable', array(
            'label' => Mage::helper('seoautolink')->__('Disable'),
            'url' => $this->getUrl('*/*/massDisable'),
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('seoautolink')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('seoautolink')->__('Are you sure?'),
        ));

        return $this;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
