<?php

class Craftsvilla_Sellerqualitycraftsvilla_Block_Adminhtml_Sellerqualitycraftsvilla_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('sellerqualitycraftsvillaGrid');
      $this->setDefaultSort('vendor_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('sellerqualitycraftsvilla/sellerqualitycraftsvilla')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      
      $this->addColumn('sellerquality_id', array(
          'header'    => Mage::helper('sellerqualitycraftsvilla')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'sellerquality_id',
      ));
      $this->addColumn('vendor_id', array(
          'header'    => Mage::helper('sellerqualitycraftsvilla')->__('vendor ID'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'vendor_id',
          'sortable'  => 'true',
      ));
      $this->addColumn('vendor_name', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('Vendor Name'),
			'width'     => '150px',
			'index'     => 'vendor_name',
			'align'     => 'left',
			'sortable'  => 'true',
      ));
      $this->addColumn('total_shipments_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('shipments90DAY'),
			'width'     => '150px',
			'index'     => 'total_shipments_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('total_shipments_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('shipments30DAY'),
			'width'     => '150px',
			'index'     => 'total_shipments_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('refund_ratio_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('refund%90DAY'),
			'width'     => '150px',
			'index'     => 'refund_ratio_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('refund_ratio_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('refund%30DAY'),
			'width'     => '150px',
			'index'     => 'refund_ratio_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('dispute_ratio_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('dispute%90DAY'),
			'width'     => '150px',
			'index'     => 'dispute_ratio_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('dispute_ratio_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('dispute%30DAY'),
			'width'     => '150px',
			'index'     => 'dispute_ratio_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('dispatch_prepaid_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('dispatchprepaid90'),
			'width'     => '150px',
			'index'     => 'dispatch_prepaid_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('dispatch_prepaid_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('dispatchprepaid30'),
			'width'     => '150px',
			'index'     => 'dispatch_prepaid_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('cod_return_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('codreturn90'),
			'width'     => '150px',
			'index'     => 'cod_return_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('cod_return_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('codreturn30'),
			'width'     => '150px',
			'index'     => 'cod_return_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('cod_cancel_90_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('codcancel90'),
			'width'     => '150px',
			'index'     => 'cod_cancel_90_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('cod_cancel_30_days', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('codcancel30'),
			'width'     => '150px',
			'index'     => 'cod_cancel_30_days',
			'sortable'  => 'true',
      ));
      $this->addColumn('cod_ratio', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('codratio'),
			'width'     => '150px',
			'index'     => 'cod_ratio',
			'sortable'  => 'true',
      ));
      $this->addColumn('craftsvilla_seller_rating', array(
			'header'    => Mage::helper('sellerqualitycraftsvilla')->__('sellerRating'),
			'width'     => '150px',
			'index'     => 'craftsvilla_seller_rating',
			'sortable'  => 'true',
      ));
      


	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('sellerqualitycraftsvilla')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/
	  
        //$this->addColumn('action',
            //array(
                //'header'    =>  Mage::helper('sellerqualitycraftsvilla')->__('Action'),
                //'width'     => '100',
                //'type'      => 'action',
                //'getter'    => 'getId',
                //'actions'   => array(
                    //array(
                        //'caption'   => Mage::helper('sellerqualitycraftsvilla')->__('Edit'),
                      //  'url'       => array('base'=> '*/*/edit'),
                    //    'field'     => 'sellerquality_id'
                  //  )
                //),
                //'filter'    => false,
              //  'sortable'  => false,
            //'index'     => 'stores',
          //    'is_system' => true,
        //));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('sellerqualitycraftsvilla')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('sellerqualitycraftsvilla')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('sellerquality_id');
        $this->getMassactionBlock()->setFormFieldName('sellerqualitycraftsvilla');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('sellerqualitycraftsvilla')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('sellerqualitycraftsvilla')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('modify', array(
        'label'    => Mage::helper('sellerqualitycraftsvilla')->__('Modify Data'),
        'width' => '-50px',
            'url'      => $this->getUrl('*/*/massModify'),
            'additional' => array(
            'visibility' => array(
            'name' => 'vendorId',
            'required' => true,
            'type' => 'text',
            'width'     => '1000px',
            'label' => Mage::helper('sellerqualitycraftsvilla')->__('VendorId  '),//'<font style="color:#ff0000">'.'*'."</font>"),
            
            
           ),array(
            'name' => 'vendorName',
            'type' => 'text',
            'label' => Mage::helper('sellerqualitycraftsvilla')->__('VendorName'),
           )
           
           )
		 ));

        //$statuses = Mage::getSingleton('sellerqualitycraftsvilla/status')->getOptionArray();

       // array_unshift($statuses, array('label'=>'', 'value'=>''));
        //$this->getMassactionBlock()->addItem('status', array(
          //   'label'=> Mage::helper('sellerqualitycraftsvilla')->__('Change status'),
            // 'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             //'additional' => array(
               //     'visibility' => array(
                 //        'name' => 'status',
                   //      'type' => 'select',
                     //    'class' => 'required-entry',
                       //  'label' => Mage::helper('sellerqualitycraftsvilla')->__('Status'),
                        // 'values' => $statuses
                     //)
            // )
       // ));
        return $this;
    }

  public function getRowUrl($row)
  {
      //return $this->getUrl('*/*/edit', array('sellerquality_id' => $row->getId()));
  }
  public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}


}
