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

class Unirgy_Dropship_Block_Adminhtml_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udropship/vendor')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');
        $this->addColumn('vendor_id', array(
            'header'    => $hlp->__('Vendor ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'vendor_id',
            'type'      => 'number',
        ));

        $this->addColumn('vendor_name', array(
            'header'    => $hlp->__('Vendor Name'),
            'index'     => 'vendor_name',
        ));

        $this->addColumn('email', array(
            'header'    => $hlp->__('Email'),
            'index'     => 'email',
        ));
        
        //$this->addColumn('shop_name', array(
          //  'header'    => $hlp->__('Shop Name'),
          //  'index'     => 'shop_name',
          //  'renderer' => 'udropship/renderer_shopname',
        //));
        
        $this->addColumn('created_at', array(
            'header'    => $hlp->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'date',
        ));
                
        $this->addColumn('city', array(
            'header'    => $hlp->__('City'),
            'index'     => 'city',
        ));
        
        //$this->addColumn('no_of_items', array(
          //  'header'    => $hlp->__('No. Of Products'),
            //'index'     => 'no_of_items',
            //'renderer' => 'udropship/renderer_noofproducts',
        //));
        
        //$this->addColumn('bank_name', array(
        //    'header'    => $hlp->__('Bank Name'),
        //    'index'     => 'bank_name',
        //    'renderer' => 'udropship/renderer_bankname',
       // ));
        
        //$this->addColumn('ifsc_code', array(
        //    'header'    => $hlp->__('IFSC Code of Bank'),
        //    'index'     => 'ifsc_code',
       //     'renderer' => 'udropship/renderer_ifsccode',
       // ));
        
        $this->addColumn('bank_acc_no', array(
            'header'    => $hlp->__('Bank Account Number'),
            'index'     => 'bank_acc_no',
            'renderer' => 'udropship/renderer_accountnumber',
        ));
        
        //$this->addColumn('cheque', array(
           // 'header'    => $hlp->__('Cheque to be made in Name of'),
           // 'index'     => 'cheque',
           // 'renderer' => 'udropship/renderer_cheque',
        //));
        
       // $this->addColumn('commision', array(
          //  'header'    => $hlp->__('Commission'),
         //   'index'     => 'commision',
         //   'renderer' => 'udropship/renderer_commission',
        //));
        
       // $this->addColumn('manage_shipping', array(
          //  'header'    => $hlp->__('Manage Shipping'),
          //  'index'     => 'manage_shipping',
         //   'type'      => 'options',
         //   'options'   => Mage::getSingleton('udropship/source')->setPath('manage_shipping')->toOptionHash(),
        //));
        
        $this->addColumn('telephone', array(
            'header'    => $hlp->__('Telephone'),
            'index'     => 'telephone',
        ));
// Add an extra field of Closing balance to vendor panel By Dileswar on dated 20-02-2013		
		$this->addColumn('closing_balance', array(
            'header'    => $hlp->__('Closing Balance'),
			'index'     => 'closing_balance',
            'currency'  => 'base_currency_code',
			'currency_code' => Mage::getStoreConfig('currency/options/base'),
			));
        
       


       // $this->addColumn('carrier_code', array(
        //    'header'    => $hlp->__('Used Carrier'),
        //    'index'     => 'carrier_code',
        //    'type'      => 'options',
        //    'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(),
        //));

       // $this->addColumn('status', array(
         //   'header'    => $hlp->__('Status'),
        //    'index'     => 'status',
        //    'type'      => 'options',
        //    'options'   => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionHash(),
       // ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('vendor');

       // $this->getMassactionBlock()->addItem('delete', array(
         //    'label'=> Mage::helper('udropship')->__('Delete'),
           //  'url'  => $this->getUrl('*/*/massDelete'),
             //'confirm' => Mage::helper('udropship')->__('Are you sure?')
        //));

        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('udropship')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'status' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('udropship')->__('Status'),
                         'values' => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionArray(true),
                     )
             )
        ));

       $this->getMassactionBlock()->addItem('disableShop', array(
             'label'=> Mage::helper('udropship')->__('Disable Shop'),
             'url'  => $this->getUrl('*/*/disableShop'),
			 'additional' => array(
             'visibility' =>array(
								'name' => 'disable_shop',
								'class' => 'required-entry',
								'type'  => 'text',
								'label' => Mage::helper('customerreturn')->__('Remark :Use only when disable shop'),
						 )
             )
        ));
                 

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
