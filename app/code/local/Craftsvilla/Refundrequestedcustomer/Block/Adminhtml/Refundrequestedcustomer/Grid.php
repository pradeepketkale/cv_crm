<?php

class Craftsvilla_Refundrequestedcustomer_Block_Adminhtml_Refundrequestedcustomer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('refundrequestedcustomerGrid');
      $this->setDefaultSort('refundrequestedcustomer_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer')->getCollection();
      $collection->getSelect()
      			 ->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_status'));
      			
      	//echo $collection->getSelect()->__toString(); exit;
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('refundrequestedcustomer_id', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'refundrequestedcustomer_id',
      ));

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('ShipmentID'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
      $this->addColumn('customer_name', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('CustomerName'),
          'align'     =>'left',
          'index'     => 'customer_name',
      ));
      $this->addColumn('account_number', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('AccountNumber'),
          'align'     =>'left',
          'index'     => 'account_number',
      ));
      $this->addColumn('name_on_account', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('NameOnAccount'),
          'align'     =>'left',
          'index'     => 'name_on_account',
      ));
      $this->addColumn('ifsccode', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('IFSCcode'),
          'align'     =>'left',
          'index'     => 'ifsccode',
      ));
      $this->addColumn('trackingcode', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('Trackingcode'),
          'align'     =>'left',
          'index'     => 'trackingcode',
      ));
      $this->addColumn('couriername', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('Couriername'),
          'align'     =>'left',
          'index'     => 'couriername',
      ));
	  $this->addColumn('created_at', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('CreatedTime'),
          'align'     =>'left',
          'index'     => 'created_at',
      ));
	  
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('<module>')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	 

      $this->addColumn('status', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	   */
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('refundrequestedcustomer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('refundrequestedcustomer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		$this->addColumn('refund_status', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('RefundStatus'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'refund_status',
          'type'      => 'options',
          'options'   => array(
				1 => 'Requested',
				2 => 'Approved',
				3 => 'Rejected',
				4 => 'Checked',
				
          ),
      ));
	 	$this->addColumn('remarks', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('Remarks'),
          'align'     =>'left',
          'index'     => 'remarks',
          'type'      => 'text',
      ));
	  $this->addColumn('qty', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('Qty'),
          'align'     =>'left',
          'index'     => 'qty',
          'type'      => 'text',
      ));
      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('refundrequestedcustomer')->__('ShipmentStatus'),
          'align'     =>'left',
          'index'     => 'udropship_status',
          'type'      => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
          
      ));
	 	
	 	
		//$this->addExportType('*/*/exportCsv', Mage::helper('refundrequestedcustomer')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('refundrequestedcustomer')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('refundrequestedcustomer_id');
        $this->getMassactionBlock()->setFormFieldName('refundrequestedcustomer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('refundrequestedcustomer')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('refundrequestedcustomer')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('refundrequestedcustomer/status')->getOptionArray();
       
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('refundrequestedcustomer')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('refundrequestedcustomer')->__('Status'),
                         'values' => $statuses
                     ),
                     array(
                         'name' => 'remarks',
                         'type' => 'text',
                         //'class' => 'required-entry',
                         'label' => Mage::helper('refundrequestedcustomer')->__('Remarks :Use only if Status Rejected')
                     )
             )
            
            
        )); 
        
        $remarks = Mage::getSingleton('refundrequestedcustomer/remarks');
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
