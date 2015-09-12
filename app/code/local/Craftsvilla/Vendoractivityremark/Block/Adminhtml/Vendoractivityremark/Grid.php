<?php

class Craftsvilla_Vendoractivityremark_Block_Adminhtml_Vendoractivityremark_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('vendoractivityremarkGrid');
      $this->setDefaultSort('vendoractivityremark_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('vendoractivityremark/vendoractivityremark')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('vendoractivityremark_id', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Vendor Activity Remark ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vendoractivityremark_id',
      ));

      $this->addColumn('vendorid', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Vendor ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vendorid',
      ));

     $this->addColumn('vendorname', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Vendor Name'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vendorname',
      ));

    $this->addColumn('created_at', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Created Date'),
          'align'     =>'right',
          'index'     => 'created_at',
          'filter_index' => 'main_table.created_at',
	  'type' 	  => 'date',
      ));         

      $this->addColumn('vendoractivity', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Vendor Activity'),
          'align'     =>'left',
          'index'     => 'vendoractivity',
      ));

      //$this->addColumn('catalogprivilegesremark', array(
          //'header'    => Mage::helper('vendoractivityremark')->__('Catalog Privileges Remark'),
         // 'align'     =>'left',
         // 'index'     => 'catalogprivilegesremark',
      //));

       //$this->addColumn('logisticsprivilegesremark', array(
         // 'header'    => Mage::helper('vendoractivityremark')->__('Logistics Privileges Remark'),
         // 'align'     =>'left',
         // 'index'     => 'logisticsprivilegesremark',
     // ));

    // $this->addColumn('paymentprivilegesremark', array(
          //'header'    => Mage::helper('vendoractivityremark')->__('Payment Privileges Remark'),
         // 'align'     =>'left',
         // 'index'     => 'paymentprivilegesremark',
     // ));

      

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('vendoractivityremark')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/
	  
      /*  $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('vendoractivityremark')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('vendoractivityremark')->__('Edit'),*/
                        //'url'       => array('base'=> '*/*/edit'),
                        //'field'     => 'id'
                   // )
               // ),
              //  'filter'    => false,
              //  'sortable'  => false,
              //  'index'     => 'stores',
              //  'is_system' => true,
     //   ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('vendoractivityremark')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('vendoractivityremark')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
    //    $this->setMassactionIdField('vendoractivityremark_id');
     //   $this->getMassactionBlock()->setFormFieldName('vendoractivityremark');

     //   $this->getMassactionBlock()->addItem('delete', array(
      //       'label'    => Mage::helper('vendoractivityremark')->__('Delete'),
      //       'url'      => $this->getUrl('*/*/massDelete'),
      //       'confirm'  => Mage::helper('vendoractivityremark')->__('Are you sure?')
      //  ));

      //  $statuses = Mage::getSingleton('vendoractivityremark/status')->getOptionArray();

      //  array_unshift($statuses, array('label'=>'', 'value'=>''));
      //  $this->getMassactionBlock()->addItem('status', array(
       //      'label'=> Mage::helper('vendoractivityremark')->__('Change status'),
       //      'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
       //      'additional' => array(
                 //   'visibility' => array(
                //         'name' => 'status',
                //         'type' => 'select',
                 //        'class' => 'required-entry',
                  //       'label' => Mage::helper('vendoractivityremark')->__('Status'),
                 //        'values' => $statuses
                  //   )
             //)
       // ));
       // return $this;
    }

  public function getRowUrl($row)
  {
     //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
