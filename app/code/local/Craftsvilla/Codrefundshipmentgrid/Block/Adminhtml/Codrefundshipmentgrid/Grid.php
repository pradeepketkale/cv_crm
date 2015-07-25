<?php

class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentgrid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('codrefundshipmentgridGrid');
      $this->setDefaultSort('codrefundshipmentgrid_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);	
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('codrefundshipmentgrid/codrefundshipmentgrid')->getCollection();
	  $collection->getSelect()
		->joinLeft(array('sales_flat_shipment'), 'main_table.shipment_id=sales_flat_shipment.increment_id', array('udropship_status'))
		//->joinLeft(array('shipmentpayout'), 'main_table.shipment_id=shipmentpayout.shipment_id', array('payout_status'=>'shipmentpayout_status'))
//		  ->where("sales_flat_shipment.udropship_status IN('14','15','16','17')")			
		;
//echo $collection->getSelect()->__toString();exit;
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('codrefundshipmentgrid_id', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'codrefundshipmentgrid_id',
      ));

      /*$this->addColumn('order_id', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Order Id'),
          'align'     =>'left',
          'index'     => 'order_id',
		'filter_index' => 'main_table.order_id'	
      ));*/
	  
	$this->addColumn('shipment_id', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Shipmentid Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
		'filter_index' => 'main_table.shipment_id'		
      ));
	$this->addColumn('cust_name', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Cust Name'),
          'align'     =>'left',
          'index'     => 'cust_name',
		'filter_index' => 'main_table.cust_name'		
      ));
	$this->addColumn('accountno', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Account No.'),
          'align'     =>'left',
          'index'     => 'accountno',
		  'filter'    => false,	
      ));
	$this->addColumn('ifsccode', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('IFSC Code'),
          'align'     =>'left',
          'index'     => 'ifsccode',
			'filter'    => false,	
      ));
	$this->addColumn('paymentamount', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Payment Amount'),
          'align'     =>'left',
          'index'     => 'paymentamount',
          'filter'    => false,			
      ));


      $this->addColumn('udropship_status', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Shipment Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'udropship_status',
          'type'      => 'options',
          //'options'   => array(
              //1 => 'Enabled',
             // 2 => 'Disabled',
         // ),
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
		 'filter'    => false,			
      ));
/*$this->addColumn('payout_status', array(
          'header'    => Mage::helper('codrefundshipmentgrid')->__('Payout Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'payout_status',
          'type'      => 'options',
          'options'   => array(
		  	  2 => 'Refunded',	
              1 => 'Paid',
              0 => 'Unpaid',
          ),
      ));*/

	  
        //$this->addColumn('action',
          //  array(
            //    'header'    =>  Mage::helper('codrefundshipmentgrid')->__('Action'),
              //  'width'     => '100',
                //'type'      => 'action',
                //'getter'    => 'getId',
                //'actions'   => array(
                  //  array(
                    //    'caption'   => Mage::helper('codrefundshipmentgrid')->__('Edit'),
                      //  'url'       => array('base'=> '*/*/edit'),
                        //'field'     => 'id'
                    //)
                //),
                //'filter'    => false,
                //'sortable'  => false,
                //'index'     => 'stores',
                //'is_system' => true,
        //));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
	//	$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('codrefundshipmentgrid_id');
        $this->getMassactionBlock()->setFormFieldName('codrefundshipmentgrid');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('codrefundshipmentgrid')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('codrefundshipmentgrid')->__('Are you sure?')
        ));
        //$this->getMassactionBlock()->addItem('modify', array(
            // 'label'    => Mage::helper('codrefundshipmentgrid')->__('Modify Data'),
          //   'url'      => $this->getUrl('*/*/massModify'),
             //'confirm'  => Mage::helper('codrefundshipmentgrid')->__('Are you sure?')
        //));
        $this->getMassactionBlock()->addItem('modify', array(
        'label'    => Mage::helper('codrefundshipmentgrid')->__('Modify Data'),
            'url'      => $this->getUrl('*/*/massModify'),
            'additional' => array(
            'visibility' => array(
            'name' => 'shipmentId',
            'class' => 'required-entry ""',
            'required' => true,
            'type' => 'text',
            'label' => Mage::helper('codrefundshipmentgrid')->__('ShipmentId'),//'<font style="color:#ff0000">'.'*'."</font>"),
            
            
           ),array(
            'name' => 'custName',
            'type' => 'text',
            'label' => Mage::helper('codrefundshipmentgrid')->__('CustName'),
           ),array(
            'name' => 'accountNumber',
            'type' => 'text',
            'label' => Mage::helper('codrefundshipmentgrid')->__('accountNumber'),
           ),array(
            'name' => 'ifscCode',
            'type' => 'text',
            'label' => Mage::helper('codrefundshipmentgrid')->__('ifscCode'),
           ),array(
            'name' => 'amount',
            'type' => 'text',
            'label' => Mage::helper('codrefundshipmentgrid')->__('amount'),
           ),
           
           )
		 ));
        

       // $statuses = Mage::getSingleton('codrefundshipmentgrid/status')->getOptionArray();
//$statuses = array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid');
  //      array_unshift($statuses, array('label'=>'', 'value'=>''));
    //    $this->getMassactionBlock()->addItem('status', array(
      //       'label'=> Mage::helper('codrefundshipmentgrid')->__('Change status'),
        //     'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
          //   'additional' => array(
            //        'visibility' => array(
              //           'name' => 'payout_status',
                //         'type' => 'select',
                  //       'class' => 'required-entry',
                    //     'label' => Mage::helper('codrefundshipmentgrid')->__('Payout Status'),
                      //   'values' => array('2'=>'Refunded','1'=>'Paid', '0'=>'Unpaid'),
                     //)
             //)
        //));
		
		$this->getMassactionBlock()->addItem('exporttoexcel', array(
             'label'=> Mage::helper('codrefundshipmentgrid')->__('Download Refund COD'),
             'url'  => $this->getUrl('*/*/codrefundshipupload', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'selected_date',
                         'type' => 'date',
                         'class' => 'required-entry',
                         'format' 	  => 'yyyy-MM-dd',
          				 'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  				 'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
                         'label' => Mage::helper('codrefundshipmentgrid')->__('Date')
                     )
             )
        ));

        return $this;
    }
  public function getRowUrl($row)
  {
      //return $this->getUrl('*/*/editNew', array('id' => $row->getId()));
  }
public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}

}
