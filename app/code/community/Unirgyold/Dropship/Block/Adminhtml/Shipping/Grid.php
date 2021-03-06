<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Shipping_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('shippingGrid');
        $this->setDefaultSort('days_in_transit');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('shipping_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('udropship')->getShippingMethods();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');

        $this->addColumn('shipping_code', array(
            'header'    => $hlp->__('Method Code'),
            'index'     => 'shipping_code',
        ));

        $this->addColumn('shipping_title', array(
            'header'    => $hlp->__('Method Title'),
            'index'     => 'shipping_title',
        ));

        $this->addColumn('days_in_transit', array(
            'header'    => $hlp->__('Days In Transit'),
            'index'     => 'days_in_transit',
        ));

        $this->addColumn('website_ids', array(
            'header'        => Mage::helper('cms')->__('Website'),
            'index'         => 'website_ids',
            'type'          => 'options',
            'options'       => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            'filter_condition_callback'
                            => array($this, '_filterWebsiteCondition'),
        ));

        $this->addColumn('system_methods', array(
            'header'    => $hlp->__('System Methods'),
            'index'     => 'system_methods',
            'filter'    => false,
            'sortable'  => false,
        ));

        $column = $this->getColumn('system_methods');
        $column->setRenderer($this->getLayout()->createBlock('udropship/adminhtml_shipping_grid_renderer')->setColumn($column));

        $column = $this->getColumn('website_ids');
        $column->setRenderer($this->getLayout()->createBlock('udropship/adminhtml_shipping_grid_renderer')->setColumn($column));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _filterWebsiteCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addWebsiteFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shipping');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('udropship')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('udropship')->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
