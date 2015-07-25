<?php

class Craftsvilla_Shipmentlite_Block_Adminhtml_Shipmentlite_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
	parent::__construct();
	$this->setId('shipmentliteGrid');
	$this->setDefaultSort('increment_id');
	$this->setDefaultDir('DESC');
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);
  }



  protected function _prepareCollection()
  {
      $collection = Mage::getModel('sales/order_shipment')->getCollection();
     
     
      /*echo $collection->getSelect()->__toString();
      exit();
      /*echo "<pre>";
      print_r($collection->getData());
      exit();*/					
      $this->setCollection($collection);
	$collection->getSelect();
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

        $this->addColumn('View Details',
            array(
                'header'    => Mage::helper('shipmentlite')->__('View Details'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('shipmentlite')->__('View'),
                        'url'     => array('base'=>'adminhtml/sales_shipment/view'),
                        'field'   => 'shipment_id',
			'target' => '_blank'
                    )
                ),
               
        ));

      $this->addColumn('increment_id', array(
          'header'    => Mage::helper('shipmentlite')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'increment_id',
      ));
      
      $this->addColumn('created_at', array(
          'header'    => Mage::helper('shipmentlite')->__('Date Shipped'),
          'align'     =>'left',
          'index'     => 'created_at',
      	  'filter_index' => 'main_table.created_at',
	  'type' 	  => 'datetime',	
      ));

      $this->addColumn('updated_at', array(
          'header'    => Mage::helper('shipmentlite')->__('Date Updated'),
          'align'     => 'left',
          'index'     => 'updated_at',
          'filter_index'      => 'main_table.updated_at',
	  'type' 	  => 'datetime',          
      ));

      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('shipmentlite')->__('Status'),
          'align'     => 'left',
          'index'     => 'udropship_status',
          'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
          //'filter' => 'udropship/vendor_gridColumnFilter'  
      ));      


      $this->addColumn('total_qty', array(
          'header'    => Mage::helper('shipmentlite')->__('Total Qty'),
          'align'     => 'left',
          'index'     => 'total_qty',
          'type' => 'number',       
      ));

      $this->addColumn('base_total_value', array(
          'header'    => Mage::helper('shipmentlite')->__('Grand Total'),
          'align'     => 'left',
          'index'     => 'base_total_value',
	  'type'  => 'price',
          'currency' => 'base_currency_code',
          'currency_code' => Mage::getStoreConfig('currency/options/base'),
                    
      ));


     return parent::_prepareColumns();
  }

   protected function _prepareMassaction()
    {
        //$this->setMassactionIdField('shipmentlite_id');
        //$this->getMassactionBlock()->setFormFieldName('shipmentlite');
		
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
	   
		
			
		
		//$this->getMassactionBlock()->addItem();	
			
       // return $this;
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
