<?php

class Craftsvilla_Shipmentpayout_Block_Adminhtml_Shipmentpayout_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('shipmentpayoutGrid');
      $this->setDefaultSort('shipmentpayout_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      // Dileswar added a one joint table of merchant_id_city to get the values on grid column...of shipment payout..
	  $collection->getSelect()
      				//->join(array('a'=>'sales_flat_shipment_grid'), 'main_table.shipment_id=a.increment_id', array('a.udropship_status'));
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'udropship_status', 'shipping_amount'=>'base_shipping_amount', 'subtotal'=>'base_total_value', 'total_cost'=>'total_cost', 'tax_amount'=>'base_tax_amount', 'commission_percent'=>'commission_percent'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
				->join(array('c'=>'udropship_vendor'),'c.vendor_id= a.udropship_vendor',array('merchant_id_city'))
				->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
      			->columns(array(
      						'base_discount_amount'=>$this->_getFlatExpressionColumn('base_discount_amount')
      			));
      //$collection->getSelect()->order('a.increment_id DESC');			
      /*echo $collection->getSelect()->__toString();
      exit();*/
      /*echo "<pre>";
      print_r($collection->getData());
      exit();*/					
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('shipmentpayout_id', array(
          'header'    => Mage::helper('shipmentpayout')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'shipmentpayout_id',
      ));

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('shipmentpayout')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
      
      $this->addColumn('order_id', array(
          'header'    => Mage::helper('shipmentpayout')->__('Order Id'),
          'align'     =>'left',
          'index'     => 'order_id',
      	  'filter_index' => 'main_table.order_id'	
      ));
      
      $this->addColumn('order_created_at', array(
          'header'    => Mage::helper('shipmentpayout')->__('Order Date'),
          'align'     =>'left',
          'index'     => 'order_created_at',
          'type' 	  => 'datetime',
      ));

	  	 
		$this->addColumn('method', array(
		    'header'    => Mage::helper('sales')->__('Payment Method Name'),
		    'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('free' => 'No Payment Information Required','checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','ccavenue_standard' => 'Ccavenue Old','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout','retailpay'=>'Retail Pay'),
		));

      $this->addColumn('shipmentpayout_update_time', array(
          'header'    => Mage::helper('shipmentpayout')->__('Payment Date'),
          'align'     =>'left',
          'index'     => 'shipmentpayout_update_time',
          'type' 	  => 'datetime',
      ));
// Add a comment of Refund By Dileswar on dated 19-02-2013
      $this->addColumn('shipmentpayout_status', array(
          'header'    => Mage::helper('shipmentpayout')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'shipmentpayout_status',
          'type'      => 'options',
          'options'   => array(
		  	  2 => 'Refunded',	
              1 => 'Paid',
              0 => 'Unpaid',
          ),
      ));
      
      $this->addColumn('citibank_utr', array(
          'header'    => Mage::helper('shipmentpayout')->__('CitiBank UTR No.'),
          'align'     =>'left',
          'index'     => 'citibank_utr',
      ));
      
      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('shipmentpayout')->__('PO Status'),
          'align'     =>'left',
          'index'     => 'udropship_status',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
      ));
      
      $this->addColumn('udropship_vendor', array(
          'header'    => Mage::helper('shipmentpayout')->__('Vendor'),
          'align'     =>'left',
          'index'     => 'udropship_vendor',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
      $this->addColumn('refundtodo', array(
          'header'    => Mage::helper('shipmentpayout')->__('Refund To Do'),
          'align'     =>'left',
          'index'     => 'refundtodo',
		  'type'  	  => 'price',
		  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));
      $this->addColumn('subtotal', array(
            'header' => Mage::helper('shipmentpayout')->__('Subtotal'),
            'index' => 'subtotal',
			'renderer' => 'shipmentpayout/renderer_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
        
     /* $this->addColumn('tax_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Tax Amount'),
            'index' => 'tax_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));*/
		$this->addColumn('payment_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Payment Amount'),
            'index' => 'payment_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	        $this->addColumn('couponcode', array(
            'header' => Mage::helper('shipmentpayout')->__('Coupon'),
            'index' => 'couponcode',
            'type'  => 'text',
       ));
	       $this->addColumn('discount', array(
            'header' => Mage::helper('shipmentpayout')->__('Coupon Amount'),
            'index' => 'discount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	  
	  $this->addColumn('commission_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Commission Amount'),
            'index' => 'commission_amount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

      $this->addColumn('shipping_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Shipping Amount'),
            'index' => 'shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));
	  $this->addColumn('type',array(
	   		'header' => Mage::helper('shipmentpayout')->__('Payment Type'),
			'index'  => 'type',
	   ));
	   $this->addColumn('refunded_amount', array(
          'header'    => Mage::helper('shipmentpayout')->__('Refunded Amount'),
          'align'     =>'left',
          'index'     => 'refunded_amount',
		  'type'  	  => 'price',
		  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));
      
      /*$this->addColumn('base_discount_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Discount Amount'),
            'index' => 'base_discount_amount',
        	'filter_index' => $this->_getFlatExpressionColumn('base_discount_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));*/
      
      /*$this->addColumn('commission_percent', array(
            'header' => Mage::helper('shipmentpayout')->__('Commission Percentage'),
            'index' => 'commission_percent',
            'type'      => 'number',
       ));*/
	   $this->addColumn('merchant_id_city',array(
	   		'header' => Mage::helper('shipmentpayout')->__('Merchant_Id'),
			'index'  => 'merchant_id_city',
	   ));
	   

	  //Added By Dileswar On dated 30-03-2013 to add a extra column
	  
	  
	  /* $this->addColumn('intshipingcost', array(
          'header'    => Mage::helper('shipmentpayout')->__('InterShippingCost'),
          'align'     =>'left',
          'index'     => 'intshipingcost',
		  'type'  	  => 'price',
          'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));*/
	  $this->addColumn('adjustment',array(
	  		'header'	=>Mage::helper('shipmentpayout')->__('Adjustment Amount'),
			'index'     => 'adjustment',
			'align'     => 'left',
			'type'  	=> 'price',
			'currency'  => 'base_currency_code',
			'currency_code' => Mage::getStoreConfig('currency/options/base'),
	  ));
	  
	  $this->addColumn('comment',array(
	  		'header'	=>Mage::helper('shipmentpayout')->__('Comment'),
			'index'     => 'comment',
			'align'     => 'left',
			'width'     => '150px',
	  ));
      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('shipmentpayout')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('shipmentpayout')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
      ));
        
      //$this->addExportType('*/*/exportShipmentpayoutCsv', Mage::helper('adminhtml')->__('CSV'));
      //$this->addExportType('*/*/exportShipmentpayoutXml', Mage::helper('adminhtml')->__('XML'));

		
	  $this->addExportType('*/*/exportCsv', Mage::helper('shipmentpayout')->__('CSV'));
	  $this->addExportType('*/*/exportXml', Mage::helper('shipmentpayout')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('shipmentpayout_id');
        $this->getMassactionBlock()->setFormFieldName('shipmentpayout');
		
		///$this->getMassactionBlock()->addItem('delete', array(
        ///     'label'    => Mage::helper('web')->__('Delete'),
        ///     'url'      => $this->getUrl('*/*/massDelete'),
        ///     'confirm'  => Mage::helper('web')->__('Are you sure?')
        ///));

        //$statuses = Mage::getSingleton('shipmentpayout/status')->getOptionArray();
       
        $statuses = array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid');
        
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('shipmentpayout_status', array(
             'label'=> Mage::helper('shipmentpayout')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('shipmentpayout')->__('Status'),
                         'values' => array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid')
                     ), array(
                         'name' => 'updatedate',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     ),array(
                         'name' => 'utrno',
                         'type' => 'text',
                         'label' => Mage::helper('shipmentpayout')->__('UTR No.')
                     )
             )
        ));
        
        $this->getMassactionBlock()->addItem('report1', array(
             'label'=> Mage::helper('shipmentpayout')->__('Nodal Report'),
             'url'  => $this->getUrl('*/*/report1', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     )
             )
        ));
        
        $this->getMassactionBlock()->addItem('report2', array(
             'label'=> Mage::helper('shipmentpayout')->__('Report2'),
             'url'  => $this->getUrl('*/*/report2', array('_current'=>true)),
        ));
		
		$this->getMassactionBlock()->addItem('atxtreport', array(
             'label'=> Mage::helper('shipmentpayout')->__('Non-Nodal Report'),
             'url'  => $this->getUrl('*/*/atxtreport', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     )
             )
        ));
        
		$this->getMassactionBlock()->addItem('codreport', array(
             'label'=> Mage::helper('shipmentpayout')->__('COD Report'),
             'url'  => $this->getUrl('*/*/codreport', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     )
             )
        ));

    $this->getMassactionBlock()->addItem('paypalreport', array(
             'label'=> Mage::helper('shipmentpayout')->__('PayPal Report'),
             'url'  => $this->getUrl('*/*/paypalreport', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format'     => 'yyyy-MM-dd',
                   'image'    => $this->getSkinUrl('images/grid-cal.gif'), 
               'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('shipmentpayout')->__('Date')
                     )
             )
      ));
        //$this->getMassactionBlock()->addItem('updateshipmentpayout', array(
        //     'label'=> Mage::helper('shipmentpayout')->__('Change Date'),
        //     'url'  => $this->getUrl('*/*/massDate', array('_current'=>true)),
        //     'additional' => array(
        //            'visibility' => array(
        //                 'name' => 'updatedate',
        //                 'type' => 'date',
        //                 'class' => 'required-entry',
       //                  'format' 	  => 'yyyy-MM-dd',
         // 				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		 // 				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
         //                'label' => Mage::helper('shipmentpayout')->__('Date')
                         //'values' => $statuses
         //            )
         //    )
       // ));
	  
	 // Added By Dileswar On dated 30-0-2013   
	   $this->getMassactionBlock()->addItem('refundedamount', array(
                 'label'=> Mage::helper('shipmentpayout')->__('Amount To Refund'),
                 'url'  => $this->getUrl('*/*/refundedamount', array('_current'=>true)),
				 'additional' => array(
                    'visibility' =>array(
                         'name' => 'refunded_amount',
                         'type'  => 'text',
						 'label' => Mage::helper('shipmentpayout')->__('Amount'),
						 )
             )
            ));
		$this->getMassactionBlock()->addItem('autorefund', array(
                 'label'=> Mage::helper('shipmentpayout')->__('Auto Refund'),
                 'url'  => $this->getUrl('*/*/autorefund', array('_current'=>true)),
				 
            ));	
			
		$this->getMassactionBlock()->addItem('autorefundpayu', array(
                 'label'=> Mage::helper('shipmentpayout')->__('Auto Refund Payu'),
                 'url'  => $this->getUrl('*/*/autorefundpayu', array('_current'=>true)),
				 
            ));	
		$this->getMassactionBlock()->addItem('autorefundcod', array(
                 'label'=> Mage::helper('shipmentpayout')->__('Auto Refund Cod'),
                 'url'  => $this->getUrl('*/*/autorefundcod', array('_current'=>true)),
				 
            ));	
			
        return $this;
    }
	

  	public function getRowUrl($row)
  	{
    	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  	}
	public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}
  
	protected function _getFlatExpressionColumn($key, $bypass=true)
    {
    	$result = $bypass ? $key : null;
    	switch ($key) {
    		case 'base_discount_amount':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(si.base_discount_amount,0)) from sales_flat_shipment_item si where si.order_id=a.order_id)");
				break;
    	}
    	return $result;
    }
}
