<?php

class Craftsvilla_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('qualitycheckshipmentGrid');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
	{
      $collection = Mage::getModel('sales/order_shipment_item')->getCollection()->setOrder('entity_id','DESC');
		$collection->getSelect()
	      ->joinLeft(array('sales_flat_shipment'), 'main_table.parent_id=sales_flat_shipment.entity_id', array('increment_id','udropship_status'))
		  ->where("sales_flat_shipment.udropship_status IN('14','15','16','17')")			
		;
        $this->setCollection($collection);
      return parent::_prepareCollection();
	}

  protected function _prepareColumns()
  {
	$this->addColumn('image', array(
			'header'    => Mage::helper('qualitycheckshipment')->__('Image'),
			'align'     =>'left',
			'index'     => 'product_id',
			'width'     => '100px',
			//'type' 		=>'image',
			//'filter_index' => $image,
			'renderer'  => 'Craftsvilla_Qualitycheckshipment_Block_Imageshipment'
        ));

      $this->addColumn('increment_id', array(
          'header'    => Mage::helper('qualitycheckshipment')->__('Shipment Id'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'increment_id',
      ));

	$this->addColumn('sku', array(
          'header'    => Mage::helper('qualitycheckshipment')->__('SKU'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'sku',
      ));
	$this->addColumn('name', array(
          'header'    => Mage::helper('qualitycheckshipment')->__('Product Name'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'name',
      ));
	$this->addColumn('qty', array(
          'header'    => Mage::helper('qualitycheckshipment')->__('Qty'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'qty',
      ));
	$this->addColumn('price', array(
          'header'    => Mage::helper('qualitycheckshipment')->__('Price'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'price',
      ));
	
$this->addColumn('udropship_status', array(
          'header'    => Mage::helper('shipmentpayout')->__('PO Status'),
          'align'     =>'left',
          'index'     => 'udropship_status',
      	  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
      ));
 //$this->addColumn('action',
   //         array(
     //           'header'    =>  Mage::helper('qualitycheckshipment')->__('Action'),
       //         'width'     => '100',
         //       'type'      => 'action',
           //     'getter'    => 'getId',
             //   'actions'   => array(
               //     array(
                 //       'caption'   => Mage::helper('qualitycheckshipment')->__('Edit'),
                   //     'url'       => array('base'=> '*/*/edit'),
                     //   'field'     => 'id'
                    //)
                //),
                //'filter'    => false,
                //'sortable'  => false,
                //'index'     => 'stores',
                //'is_system' => true,
        //));
		
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('qualitycheckshipment_id');
        $this->getMassactionBlock()->setFormFieldName('qualitycheckshipment');

      //  $this->getMassactionBlock()->addItem('delete', array(
       //      'label'    => Mage::helper('qualitycheckshipment')->__('Delete'),
        //     'url'      => $this->getUrl('*/*/massDelete'),
         //    'confirm'  => Mage::helper('qualitycheckshipment')->__('Are you sure?')
       // ));

       // $statuses = Mage::getSingleton('qualitycheckshipment/status')->getOptionArray();
		$statuses = array('17'=>'Received In Craftsvilla','14'=>'Charges Back', '16'=>'Qc Rejected');
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('qualitycheckshipment')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('qualitycheckshipment')->__('Status'),
                        'values' => array('0'=>' ','17'=>'Received In Craftsvilla','14'=>'Charges Back', '16'=>'Qc Rejected') 
						//'values' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
                     ),
					array(
                         'name' => 'comment',
                         'type' => 'text',
                         'label' => Mage::helper('qualitycheckshipment')->__('cmnt')
                     )
             )
        ));
        return $this;
    }

 // public function getRowUrl($row)
  //{
      //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
 // }

}
