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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Shipping extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('udropship_vendor_shipping');
        $this->setDefaultSort('days_in_transit');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    public function getVendor()
    {
        $vendor = Mage::registry('vendor_data');
        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($this->getVendorId());
        }
        return $vendor;
    }
/*
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_vendor') {
            $productIds = $this->_getSelectedMethods();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
*/
    protected function _prepareCollection()
    {
        $collection = Mage::helper('udropship')->getShippingMethods()
            ->joinVendor($this->getVendorId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');
        $this->addColumn('in_vendor', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_vendor',
            'values'    => $this->_getSelectedMethods(),
            'align'     => 'center',
            'index'     => 'shipping_id'
        ));
        $this->addColumn('shipping_code', array(
            'header'    => $hlp->__('Code'),
            'index'     => 'shipping_code'
        ));
        $this->addColumn('shipping_title', array(
            'header'    => $hlp->__('Title'),
            'index'     => 'shipping_title'
        ));

        $this->addColumn('days_in_transit', array(
            'header'    => $hlp->__('Days In Transit'),
            'index'     => 'days_in_transit'
        ));

        if ($this->getVendor()->getAllowShippingExtraCharge()) {
            $this->addColumn('_allow_extra_charge', array(
                'header'    => $this->__('Extra Charge'),
                'index'     => 'allow_extra_charge',
                'renderer'  => 'udropship/adminhtml_vendor_helper_gridRenderer_shippingExtraCharge',
                'sortable'  => false,
                'filter'    => false,
                'field_id_tpl' => '_%s',
                'vendor' => $this->getVendor()
            ));
        }

        $carriers = Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(true);
        $carriers[''] = $hlp->__('* Use Default');

        $this->addColumn('_est_carrier_code', array(
            'header'    => $hlp->__('Estimate Carrier'),
            'index'     => 'est_carrier_code',
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'select',
            'options'   => $carriers,
        ));

        $carriers['**estimate**'] = $hlp->__('* Use Estimate');

        $this->addColumn('_carrier_code', array(
            'header'    => $hlp->__('Carrier Override'),
            'index'     => 'carrier_code',
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'select',
            'options'   => $carriers,
        ));

        if ($this->getVendor()->getAllowShippingExtraCharge()) {
            $this->addColumn('_priority', array(
                'header'    => $hlp->__('Priority'),
                'index'     => 'priority',
                'sortable'  => false,
                'filter'    => false,
                'editable'  => true, 'edit_only'=>true,
            ));
        }

        $this->addColumn('_default', array(
            'header'    => $hlp->__('Default'),
            'index'     => 'shipping_id',
            'sortable'  => false,
            'filter'    => false,
            'editable'  => true, 'edit_only'=>true,
            'type'      => 'radio',
            'html_name' => 'default_shipping_id',
            'value'     => $this->getVendor()->getDefaultShippingId()
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/shippingGrid', array('_current'=>true));
    }

    protected function _getSelectedMethods()
    {
        $json = $this->getRequest()->getPost('vendor_shipping');
        if (!is_null($json)) {
            $methods = array_keys((array)Zend_Json::decode($json));
        } else {
            $methods = array_keys($this->getVendor()->getAssociatedShippingMethods());
        }
        return $methods;
    }
}
