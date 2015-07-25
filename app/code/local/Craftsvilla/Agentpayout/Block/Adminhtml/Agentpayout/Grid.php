<?php

class Craftsvilla_Agentpayout_Block_Adminhtml_Agentpayout_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('agentpayoutGrid');
      $this->setDefaultSort('agentpayout_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('agentpayout/agentpayout')->getCollection();
      
	  $collection->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'udropship_status', 'itemised_total_shippingcost', 'subtotal'=>'base_total_value', 'total_cost'=>'total_cost', 'commission_percent'=>'commission_percent','agent_id'))
      			//->join(array('b'=>'sales_flat_shipment_item'), 'b.order_id=a.shipment_id', array('order_created_at'))
				->join(array('c'=>'udropship_vendor'),'c.vendor_id= a.udropship_vendor',array('merchant_id_city'))
				->join(array('d'=>'sales_flat_order'), 'd.entity_id=a.order_id', array('d.coupon_code'))
				->joinLeft('sales_flat_order_payment', 'a.order_id = sales_flat_order_payment.parent_id','method');
      			
      //$collection->getSelect()->order('a.increment_id DESC');			
      //echo $collection->getSelect()->__toString();
      //exit();
      /*echo "<pre>";
      print_r($collection->getData());
      exit();*/					
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('agentpayout_id', array(
          'header'    => Mage::helper('agentpayout')->__('Payout ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'agentpayout_id',
      ));

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('agentpayout')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
		$this->addColumn('method', array(
		    'header'    => Mage::helper('sales')->__('Payment Method Name'),
		    'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('free' => 'No Payment Information Required','checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','ccavenue_standard' => 'Ccavenue Old','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout'),
		));

      $this->addColumn('agentpayout_update_time', array(
          'header'    => Mage::helper('agentpayout')->__('Payment Date'),
          'align'     =>'left',
          'index'     => 'agentpayout_update_time',
          'type' 	  => 'datetime',
      ));

      $this->addColumn('agentpayout_status', array(
          'header'    => Mage::helper('agentpayout')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'agentpayout_status',
          'type'      => 'options',
          'options'   => array(
		  	  2 => 'Refunded',	
              1 => 'Paid',
              0 => 'Unpaid',
          ),
      ));

      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('agentpayout')->__('Shipment Status'),
          'align'     =>'left',
          'index'     => 'udropship_status',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
      ));
      
      $this->addColumn('udropship_vendor', array(
          'header'    => Mage::helper('agentpayout')->__('Vendor'),
          'align'     =>'left',
          'index'     => 'udropship_vendor',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	  $this->addColumn('agent_id', array(
          'header'    => Mage::helper('agentpayout')->__('Agent'),
          'align'     =>'left',
          'index'     => 'agent_id',
      	  'type' => 'options',
          'options' => Mage::getSingleton('uagent/source')->setPath('agents')->toOptionHash(),
          'filter' => 'uagent/agent_gridColumnFilter'
      ));

      $this->addColumn('subtotal', array(
            'header' => Mage::helper('agentpayout')->__('Subtotal'),
            'index' => 'subtotal',
			//'renderer' => 'agentpayout/renderer_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
        
		$this->addColumn('payment_amount', array(
            'header' => Mage::helper('agentpayout')->__('Payment Amount'),
            'index' => 'payment_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	        $this->addColumn('coupon_code', array(
            'header' => Mage::helper('agentpayout')->__('Coupon'),
            'index' => 'coupon_code',
            'type'  => 'text',
       ));
	      /* $this->addColumn('couponcodeamount', array(
            'header' => Mage::helper('agentpayout')->__('Coupon percentage'),
            'index' => 'couponcodeamount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));*/
	  
	  $this->addColumn('commission_amount', array(
            'header' => Mage::helper('agentpayout')->__('Commission Amount'),
            'index' => 'commission_amount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

      $this->addColumn('itemised_total_shippingcost', array(
            'header' => Mage::helper('agentpayout')->__('Shipping Amount'),
            'index' => 'itemised_total_shippingcost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

	 /*  $this->addColumn('refunded_amount', array(
          'header'    => Mage::helper('agentpayout')->__('Refunded Amount'),
          'align'     =>'left',
          'index'     => 'refunded_amount',
		  'type'  	  => 'price',
		  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));*/
      
	   /*$this->addColumn('merchant_id_city',array(
	   		'header' => Mage::helper('agentpayout')->__('Merchant_Id'),
			'index'  => 'merchant_id_city',
	   ));*/
	  $this->addColumn('comment',array(
	  		'header'	=>Mage::helper('agentpayout')->__('Comment'),
			'index'     => 'comment',
			'align'     => 'left',
			'width'     => '150px',
	  ));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
	//	$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('agentpayout_id');
        $this->getMassactionBlock()->setFormFieldName('agentpayout');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('agentpayout')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('agentpayout')->__('Are you sure?')
        ));

         $statuses = array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid');
        
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('agentpayout_status', array(
             'label'=> Mage::helper('agentpayout')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('agentpayout')->__('Status'),
                         'values' => array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid')
                     ), array(
                         'name' => 'updatedate',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     ),
             )
        ));
        
        $this->getMassactionBlock()->addItem('agentreport', array(
             'label'=> Mage::helper('agentpayout')->__('Agent Report'),
             'url'  => $this->getUrl('*/*/agentreport', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('agentpayout')->__('Date')
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}