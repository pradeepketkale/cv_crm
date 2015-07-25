<?php

class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('disputeraisedGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('disputeraised/disputeraised')->getCollection();
       //$collection->getSelect()
                //->join(array('s'=>'sales_flat_shipment'), 's.increment_id=main_table.increment_id',array('s.increment_id'))
                //->join(array('o'=>'sales_flat_order'), 's.order_id = o.entity_id',array('o.increment_id'));
               // ->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=s.order_id and a.address_type="shipping"', array('firstname', 'lastname'))
              //  ->join(array('b'=>'udropship_vendor'), 'b.vendor_id=main_table.vendor_id', array('b.vendor_name'))
             // 	->columns(new Zend_Db_Expr("CONCAT(`firstname`, ' ',`lastname`) AS customer_name"));
				
		$collection->getSelect()->group('main_table.increment_id');
		//echo '<pre>';print_r($collection->getSelect()->__toString());exit;
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('disputeraised')->__('ID'),
          'align'     =>'right',
          'width'     => '30px',
          'index'     => 'id',
      ));

      $this->addColumn('increment_id', array(
          'header'    => Mage::helper('disputeraised')->__('Shipment ID'),
          'align'     =>'left',
         'index' => 'increment_id',
          'filter_index'   => 'increment_id',
      ));

	  /*$this->addColumn('customer_name', array(
          'header'    => Mage::helper('disputeraised')->__('Customer Name'),
          'align'     =>'left',
          'index'     => 'customer_name',
	  'filter_index' => 'CONCAT(`firstname`, " ",`lastname`)',
      ));
      
       $this->addColumn('vendor_name', array(
          'header'    => Mage::helper('disputeraised')->__('Vendor'),
          'align'     =>'left',
          'index'     => 'vendor_name',
       'filter_index' => 'b.vendor_name',
      ));*/
      
   /*   $this->addColumn('content', array(
			'header'    => Mage::helper('disputeraised')->__('Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));*/
	  
     /*  $this->addColumn('addedby', array(
			'header'    => Mage::helper('disputeraised')->__('Added By'),
			'width'     => '200px',
			'index'     => 'addedby',
	      // 'filter_index' => 'CONCAT(`firstname`, " ",`lastname`)',
      ));*/

      $this->addColumn('status', array(
          'header'    => Mage::helper('disputeraised')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Open',
              2 => 'Closed',
              3 => 'Waiting For Seller Response',
              4 => 'Waiting For Customer Response',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('disputeraised')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getIncrementId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('disputeraised')->__('View'),
                        'url'       => array('base'=> '*/*/view'),
                        'field'     => 'increment_id',
                       'target' => '_blank',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        
     // $this->addColumn('view', array(
		//	'header' => Mage::helper('disputeraised')->__('View'),
		//	'align' => 'left',
		//	'index' => 'increment_id',
		//	'renderer' => 'Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_View'
		//));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('disputeraised')->__('CSV'));
	//	$this->addExportType('*/*/exportXml', Mage::helper('disputeraised')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('disputeraised');

      /*  $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('disputeraised')->__('Delete'),*/
         //    'url'      => $this->getUrl('*/*/massDelete'),
          //   'confirm'  => Mage::helper('disputeraised')->__('Are you sure?')
       // ));

        $statuses = Mage::getSingleton('disputeraised/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('disputeraised')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('disputeraised')->__('Status'),
                         'values' => $statuses
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
