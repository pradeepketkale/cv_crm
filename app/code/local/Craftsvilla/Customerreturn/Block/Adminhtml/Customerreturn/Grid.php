<?php

class Craftsvilla_Customerreturn_Block_Adminhtml_Customerreturn_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
		$this->setId('customerreturnGrid');
		$this->setDefaultSort('customerreturn_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);			
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customerreturn/customerreturn')->getCollection();
 	  $collection->getSelect()
		->joinLeft(array('sales_flat_shipment'), 'main_table.shipment_id=sales_flat_shipment.increment_id', array('udropship_status'));
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('customerreturn_id', array(
          'header'    => Mage::helper('customerreturn')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'customerreturn_id',
      ));
	$this->addColumn('shipment_id', array(
          'header'    => Mage::helper('customerreturn')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
		'filter_index' => 'main_table.shipment_id'		
      ));

      $this->addColumn('trackingcode', array(
          'header'    => Mage::helper('customerreturn')->__('Tracking code'),
          'align'     =>'left',
          'index'     => 'trackingcode',
		  'filter_index' => 'main_table.trackingcode'	
    	 		
      ));
	$this->addColumn('couriername', array(
          'header'    => Mage::helper('customerreturn')->__('Courier Name'),
          'align'     =>'left',
          'index'     => 'couriername',
		  'filter'    => false,	
      ));
	$this->addColumn('created_at', array(
          'header'    => Mage::helper('customerreturn')->__('Created Date'),
          'align'     =>'left',
          'index'     => 'created_at',
          'type' 	  => 'datetime',
      ));
	$this->addColumn('update_at', array(
          'header'    => Mage::helper('customerreturn')->__('Updated Date'),
          'align'     =>'left',
          'index'     => 'update_at',
          'type' 	  => 'datetime',
      ));
	

      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('customerreturn')->__('Shipment Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'udropship_status',
          'type'      => 'options',
		  		 //'options'   => array(
              //1 => 'Enabled',
             // 2 => 'Disabled',
         // ),
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
		 
      ));
	$this->addColumn('status', array(
          'header'    => Mage::helper('customerreturn')->__('Return Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
				1 => 'In Transit',
				2 => 'Delivered To Seller',
				3 => 'Need Bank Details',
				4 => 'Incorrect Tracking',
          ),
      ));
$this->addColumn('remark', array(
			'header'    => Mage::helper('customerreturn')->__('Remark'),
			'width'     => '150px',
			'index'     => 'remark',
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customerreturn')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customerreturn')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('customerreturn_id');
        $this->getMassactionBlock()->setFormFieldName('customerreturn');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('customerreturn')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('customerreturn')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('customerreturn/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('customerreturn')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('customerreturn')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
	$this->getMassactionBlock()->addItem('checkcustomeremail', array(
             'label'=> Mage::helper('customerreturn')->__('Check CustomerEmail'),
             'url'  => $this->getUrl('*/*/checkcustomeremail'),
	    ));
    
    $this->getMassactionBlock()->addItem('sellercity', array(
             'label'=> Mage::helper('customerreturn')->__('Check Seller City'),
             'url'  => $this->getUrl('*/*/sellercity'),
	    ));
	$this->getMassactionBlock()->addItem('getpaymentmethod', array(
             'label'=> Mage::helper('customerreturn')->__('Check Payment Method'),
             'url'  => $this->getUrl('*/*/getpaymentmethod'),
	    ));

	$this->getMassactionBlock()->addItem('getRefundamount', array(
             'label'=> Mage::helper('customerreturn')->__('Get RefundToDo Amount'),
             'url'  => $this->getUrl('*/*/getRefundamount'),
	    ));
	$this->getMassactionBlock()->addItem('refundtodoamt', array(
             'label'=> Mage::helper('customerreturn')->__('Refund To Do'),
             'url'  => $this->getUrl('*/*/refundtodoamt'),
			 'additional' => array(
             'visibility' =>array(
								'name' => 'refund_price',
								'class' => 'required-entry',
								'type'  => 'text',
								'label' => Mage::helper('customerreturn')->__('Refund Price'),
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
