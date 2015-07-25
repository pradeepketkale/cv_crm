<?php

class Craftsvilla_Shipmentpayoutlite_Block_Adminhtml_Shipmentpayoutlite_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setId('shipmentpayoutliteGrid');
	$this->setDefaultSort('shipmentpayoutlite_id');
	$this->setDefaultDir('DESC');
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
     
     
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
    /*  $this->addColumn('shipmentpayout_id', array(
          'header'    => Mage::helper('shipmentpayout')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'shipmentpayout_id',
      )); */

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
      
      $this->addColumn('order_id', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Order Id'),
          'align'     =>'left',
          'index'     => 'order_id',
      	  'filter_index' => 'main_table.order_id'	
      ));

      $this->addColumn('shipmentpayout_status', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Status'),
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

      
      $this->addColumn('shipmentpayout_created_time', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Created Date'),
          'align'     =>'left',
          'index'     => 'shipmentpayout_created_time',
          'type' 	  => 'datetime',
      ));

      $this->addColumn('citibank_utr', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('CitiBank UTR No.'),
          'align'     =>'left',
          'index'     => 'citibank_utr',
      ));   
   
      $this->addColumn('shipmentpayout_update_time', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Payment Date'),
          'align'     =>'left',
          'index'     => 'shipmentpayout_update_time',
          'type' 	  => 'datetime',
      ));

      $this->addColumn('payment_amount', array(
            'header' => Mage::helper('shipmentpayoutlite')->__('Payment Amount'),
            'index' => 'payment_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

      $this->addColumn('couponcode', array(
            'header' => Mage::helper('shipmentpayoutlite')->__('Coupon'),
            'index' => 'couponcode',
            'type'  => 'text',
       ));

      $this->addColumn('discount', array(
            'header' => Mage::helper('shipmentpayoutlite')->__('Coupon Amount'),
            'index' => 'discount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

      $this->addColumn('commission_amount', array(
            'header' => Mage::helper('shipmentpayoutlite')->__('Commission Amount'),
            'index' => 'commission_amount',
            'type'  => 'text',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));

      $this->addColumn('type',array(
	   'header' => Mage::helper('shipmentpayoutlite')->__('Payment Type'),
	   'index'  => 'type',
	   ));

      $this->addColumn('intshipingcost', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('InterShippingCost'),
          'align'     =>'left',
          'index'     => 'intshipingcost',
	  'type'      => 'price',
          'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));


      $this->addColumn('adjustment',array(
	   'header'	=>Mage::helper('shipmentpayoutlite')->__('Adjustment Amount'),
	   'index'     => 'adjustment',
	   'align'     => 'left',
	   'type'  	=> 'price',
	   'currency'  => 'base_currency_code',
	   'currency_code' => Mage::getStoreConfig('currency/options/base'),
	  ));

      $this->addColumn('comment',array(
	   'header'	=>Mage::helper('shipmentpayoutlite')->__('Comment'),
	   'index'     => 'comment',
	   'align'     => 'left',
	   'width'     => '150px',
	  ));

      $this->addColumn('refundtodo', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Refund To Do'),
          'align'     =>'left',
          'index'     => 'refundtodo',
	  'type'      => 'price',
	  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));

      $this->addColumn('refunded_amount', array(
          'header'    => Mage::helper('shipmentpayoutlite')->__('Refunded Amount'),
          'align'     =>'left',
          'index'     => 'refunded_amount',
	  'type'      => 'price',
	  'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));

 /*     $this->addColumn('order_created_at', array(
          'header'    => Mage::helper('shipmentpayout')->__('Order Date'),
          'align'     =>'left',
          'index'     => 'order_created_at',
          'type' 	  => 'datetime',
      ));

	  	 
		$this->addColumn('method', array(
		    'header'    => Mage::helper('sales')->__('Payment Method Name'),
		    'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('free' => 'No Payment Information Required','checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','ccavenue_standard' => 'Ccavenue Old','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout'),
		));

*/
// Add a comment of Refund By Dileswar on dated 19-02-2013
      
      

  /*    
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

      $this->addColumn('subtotal', array(
            'header' => Mage::helper('shipmentpayout')->__('Subtotal'),
            'index' => 'subtotal',
			'renderer' => 'shipmentpayout/renderer_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
      ));*/
        
     /* $this->addColumn('tax_amount', array(
            'header' => Mage::helper('shipmentpayout')->__('Tax Amount'),
            'index' => 'tax_amount',
            'type'  => 'price',
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
*/

      
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
       ));
	   $this->addColumn('merchant_id_city',array(
	   		'header' => Mage::helper('shipmentpayout')->__('Merchant_Id'),
			'index'  => 'merchant_id_city',
	   ));
	   */

	  //Added By Dileswar On dated 30-03-2013 to add a extra column
	  
	  
	  /* $this->addColumn('intshipingcost', array(
          'header'    => Mage::helper('shipmentpayout')->__('InterShippingCost'),
          'align'     =>'left',
          'index'     => 'intshipingcost',
		  'type'  	  => 'price',
          'currency'  => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),	
      ));*/

	
  /*   $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('shipmentpayout')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('shipmentpayout')->__('Edit'),*/
    //                   'url'       => array('base'=> '*/*/edit'),
      /*                 'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
      ));

        */
      //$this->addExportType('*/*/exportShipmentpayoutCsv', Mage::helper('adminhtml')->__('CSV'));
      //$this->addExportType('*/*/exportShipmentpayoutXml', Mage::helper('adminhtml')->__('XML'));

		
	  //$this->addExportType('*/*/exportCsv', Mage::helper('shipmentpayout')->__('CSV'));
	  //$this->addExportType('*/*/exportXml', Mage::helper('shipmentpayout')->__('XML'));
	  
      return parent::_prepareColumns();
  }

   protected function _prepareMassaction()
    {
        $this->setMassactionIdField('shipmentpayoutlite_id');
        $this->getMassactionBlock()->setFormFieldName('shipmentpayoutlite');
		
		///$this->getMassactionBlock()->addItem('delete', array(
        ///     'label'    => Mage::helper('web')->__('Delete'),
        ///     'url'      => $this->getUrl('*/*/massDelete'),
        ///     'confirm'  => Mage::helper('web')->__('Are you sure?')
        ///));

        //$statuses = Mage::getSingleton('shipmentpayout/status')->getOptionArray();
       
       // $statuses = array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid');
        
        //array_unshift($statuses, array('label'=>'', 'value'=>''));
       
        
        
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
	   
		
			
		
		$this->getMassactionBlock()->addItem();	
			
        return $this;
    }
	

  	public function getRowUrl($row)
  	{
    	//return $this->getUrl('*/*/edit', array('id' => $row->getId()));
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
