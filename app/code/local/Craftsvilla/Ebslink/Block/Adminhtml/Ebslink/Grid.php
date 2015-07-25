<?php

class Craftsvilla_Ebslink_Block_Adminhtml_Ebslink_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('ebslinkGrid');
      $this->setDefaultSort('ebslink_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
  }

  protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }
	
	public function t($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }
	
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('ebslink/ebslink')->getCollection();
	   $this->setCollection($collection);
	  $collection->getSelect()
                ->join(array('o'=>'sales_flat_order'), 'o.entity_id=main_table.order_id', array('status'=>'o.status', 'base_grand_total'))
                ->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=o.entity_id and a.address_type="shipping"', array('firstname', 'lastname', 'telephone', 'country_id'))
                ->join(array('b'=>'sales_flat_order_payment'), 'b.parent_id=o.entity_id', array('b.method'))
				->columns(new Zend_Db_Expr("CONCAT(`firstname`, ' ',`lastname`) AS customer_name"))
				;
	 //echo $collection->getSelect()->__ToString(); exit;
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
	$this->addColumn('ebslink_id', array(
          'header'    => Mage::helper('ebslink')->__('Ebslink_Id'),
          'align'     =>'right',
          'width'     => '20px',
          'index'     => 'ebslink_id',
      ));

      $this->addColumn('order_no', array(
          'header'    => Mage::helper('ebslink')->__('Order No'),
          'align'     =>'left',
          'index'     => 'order_no',
      ));

    if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) { 
	  $this->addColumn('View Order',
                array(
                    'header'    => Mage::helper('sales')->__('View Order'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getOrderId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'adminhtml/sales_order/view'),
                            'field'   => 'order_id',
							'target' => '_blank'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
	 }
      
	  
    $this->addColumn('comment', array(
          'header'    => Mage::helper('ebslink')->__('Comments'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'comment',
          'type'      => 'options',
          'options'   => array(
              1 => 'COD Payment Needed',
              2 => 'Interested Will Pay',
			  3 => 'Not Interested Cancel',
			  4 => 'Busy Call Again',
			  5 => 'Not Reachable',
			  6 => 'International Call',
			  7 => 'Other',
          ),
      ));
	 	  
        
	   $this->addColumn('customer_name', array(
          'header'    => Mage::helper('sales')->__('Customer Name'),
          'align'     =>'left',
          'index'     => 'customer_name',
		 'filter_index' => 'CONCAT(`firstname`, " ",`lastname`)',
           
		 
		  
      ));
	  
	   $this->addColumn('telephone', array(
          'header'    => Mage::helper('sales')->__('Phone Number'),
          'align'     =>'left',
          'index'     => 'telephone',
		  'sortable'  => true,
		  
      ));
	  
	  $this->addColumn('country_id', array(
          'header'    => Mage::helper('sales')->__('Country'),
          'align'     =>'left',
          'index'     => 'country_id',
		  'width' => '50px',
		  
      ));
	  
	  $this->addColumn('base_grand_total', array(
          'header'    => Mage::helper('sales')->__('Grand Total'),
          'align'     =>'left',
          'index'     => 'base_grand_total',
		   'type'  => 'currency',
            'currency' => 'base_currency_code',
		  
      )); 
	 $this->addColumn('method', array(
            'header'    => Mage::helper('sales')->__('Payment Method'),
            'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout'),
        ));
	  
	  $this->addColumn('created_time', array(
          'header'    => Mage::helper('ebslink')->__('Created Date'),
          'align'     =>'left',
          'index'     =>'created_time',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));
	  
    $this->addColumn('status', array(
          'header'    => Mage::helper('sales')->__('Order Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
		  'filter_index' => 'o.status',
          'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
          
      ));
	  
	  $this->addColumn('ebslinkurl', array(
			'header'    => Mage::helper('ebslink')->__('Ebslinkurl'),
			'width'     => 'left',
			'index'     => 'ebslinkurl',
      ));
	  /*  $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('ebslink')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ebslink')->__('Edit'),*/
                      //  'url'       => array('base'=> '*/*/edit'),
                      /*  'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		*/
		$this->addExportType('*/*/exportCsv', Mage::helper('ebslink')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('ebslink')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
       $this->setMassactionIdField('ebslink_id');
       $this->getMassactionBlock()->setFormFieldName('ebslink');
	   
	   $this->getMassactionBlock()->addItem('resendemail', array(
             'label'    => Mage::helper('ebslink')->__('Resend Email'),
             'url'      => $this->getUrl('*/*/resendemail'),
			 'confirm'  => Mage::helper('ebslink')->__('Are you sure?')
        	    
        ));
	   
       $this->getMassactionBlock()->addItem('delete', array(
        									'label'    => Mage::helper('ebslink')->__('Delete'),
         									'url'      => $this->getUrl('*/*/massDelete'),
       										'confirm'  => Mage::helper('ebslink')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ebslink/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
        'label'=> Mage::helper('ebslink')->__('Change status'),
        'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
        	'additional' => array(
        		'visibility' => array(
        				'name' => 'status',
        				'type' => 'select',
        				'class' => 'required-entry',
        'label' => Mage::helper('ebslink')->__('Status'),
        'values' => $statuses
        )
            )
        ));
		
		 $comments = Mage::getSingleton('ebslink/comment')->getOptionArray();

        array_unshift($comments, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('comment', array(
        'label'=> Mage::helper('ebslink')->__('Change Comment'),
        'url'  => $this->getUrl('*/*/massComments', array('_current'=>true)),
        	'additional' => array(
        		'visibility' => array(
        				'name' => 'comment',
        				'type' => 'select',
        				'class' => 'required-entry',
        'label' => Mage::helper('ebslink')->__('Comment'),
        'values' => $comments
        )
            )
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
   


}
