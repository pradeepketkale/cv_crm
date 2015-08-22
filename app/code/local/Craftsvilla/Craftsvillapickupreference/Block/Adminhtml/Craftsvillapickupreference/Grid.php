<?php

class Craftsvilla_Craftsvillapickupreference_Block_Adminhtml_Craftsvillapickupreference_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('craftsvillapickupreferenceGrid');
      $this->setDefaultSort('pickup_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('craftsvillapickupreference/craftsvillapickupreference')->getCollection();
      //$collection->getSelect()
      			// ->join(array('a'=>'vendor_info_craftsvilla'), 'a.Vendor_id=main_table.vendor_name', array('udropship_status'));
      //echo "<pre>";print_r($collection);exit;
      //echo $collection->getSelect()->__toString(); exit;
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('pickup_id', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Pickup ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'pickup_id',
      ));

      $this->addColumn('Reference_Number', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Reference Number(Pickup Number)'),
          'align'     =>'left',
          'index'     => 'Reference_Number',
      ));

      $this->addColumn('created_date', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Created Date'),
          'align'     =>'left',
          'index'     => 'created_date',
      ));

     $this->addColumn('Vendor_id', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Vendor Name'),
          'align'     =>'left',
          'index'     => 'Vendor_id',
	  'type'      => 'options',
          'options'   => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
      ));

      $this->addColumn('courier_name', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Courier Name'),
          'align'     =>'left',
          'index'     => 'courier_name',
      ));

      
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      //$this->addColumn('status', array(
        //  'header'    => Mage::helper('craftsvillapickupreference')->__('Status'),
        //  'align'     => 'left',
        //  'width'     => '80px',
         // 'index'     => 'status',
         // 'type'      => 'options',
          //'options'   => array(
          //    1 => 'Enabled',
          //    2 => 'Disabled',
          //),
      //));
	  
        //$this->addColumn('action',
         //   array(
          //      'header'    =>  Mage::helper('craftsvillapickupreference')->__('Action'),
           //     'width'     => '100',
            //    'type'      => 'action',
              //  'getter'    => 'getId',
              //  'actions'   => array(
               //     array(
               //         'caption'   => Mage::helper('craftsvillapickupreference')->__('Edit'),
                //        'url'       => array('base'=> '*/*/edit'),
               //         'field'     => 'id'
               //     )
               // ),
               // 'filter'    => false,
               // 'sortable'  => false,
                //'index'     => 'stores',
                //'is_system' => true,
       // ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('craftsvillapickupreference')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('craftsvillapickupreference')->__('XML'));
	  
      return parent::_prepareColumns();
  }

   // protected function _prepareMassaction()
    //{
     //   $this->setMassactionIdField('craftsvillapickupreference_id');
      //  $this->getMassactionBlock()->setFormFieldName('craftsvillapickupreference');

      //  $this->getMassactionBlock()->addItem('delete', array(
        //     'label'    => Mage::helper('craftsvillapickupreference')->__('Delete'),
         ///    'url'      => $this->getUrl('*/*/massDelete'),
           //  'confirm'  => Mage::helper('craftsvillapickupreference')->__('Are you sure?')
        //));

       // $statuses = Mage::getSingleton('craftsvillapickupreference/status')->getOptionArray();

       // array_unshift($statuses, array('label'=>'', 'value'=>''));
       // $this->getMassactionBlock()->addItem('status', array(
       //      'label'=> Mage::helper('craftsvillapickupreference')->__('Change status'),
         //    'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             //'additional' => array(
                  //  'visibility' => array(
                   //      'name' => 'status',
                   //      'type' => 'select',
                    //     'class' => 'required-entry',
                      //   'label' => Mage::helper('craftsvillapickupreference')->__('Status'),
                      //   'values' => $statuses
                     //)
           //  )
       // ));
       // return $this;
    //}

  //public function getRowUrl($row)
  //{
   //   return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  //}

}
