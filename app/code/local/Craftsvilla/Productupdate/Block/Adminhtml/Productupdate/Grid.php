<?php

class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('productupdateGrid');
      $this->setDefaultSort('entity_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
	  
	
      //$this->setCollection($collection);
      //return parent::_prepareCollection();
     // $collection = Mage::getModel('productupdate/productupdate')->getCollection();
	//  $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {     
      $hlp = Mage::helper('productupdate');
      $hlp->searchBox();
       /*$this->addColumn('productupdate_id', array(
          'header'    => Mage::helper('productupdate')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'entity_id',
      ));

      $this->addColumn('vendorname', array(
          'header'    => Mage::helper('productupdate')->__('Vendor Name'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'udropship_vendor',
          'renderer'  => 'productupdate/adminhtml_productupdate_renderer_vendorname',
      ));
	
     /* $this->addColumn('content', array(
			'header'    => Mage::helper('productupdate')->__('Product Name'),
			'width'     => '150px',
			'index'     => 'productname',
			'renderer'  => 'productupdate/adminhtml_productupdate_renderer_productname',
      ));*/
   /*   $this->addColumn('sku', array(
          'header'    => Mage::helper('productupdate')->__('Sku'),
          'align'     =>'left',
          'width'     => '150',
          'index'     => 'sku', //MTEST11517294030
          'renderer'  => 'productupdate/adminhtml_productupdate_renderer_sku',
          
      ));
    
/*
 $this->addColumn('status', array(
          'header'    => Mage::helper('productupdate')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
       $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('productupdate')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('productupdate')->__('Edit'),
                        'url'       => array('base'=> '*//*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));*/
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('productupdate')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('productupdate')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('productupdate');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productupdate')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productupdate')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('productupdate/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('productupdate')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('productupdate')->__('Status'),
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

}
