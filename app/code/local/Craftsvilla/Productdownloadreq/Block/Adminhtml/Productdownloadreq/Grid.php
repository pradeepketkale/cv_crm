<?php

class Craftsvilla_Productdownloadreq_Block_Adminhtml_Productdownloadreq_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('productdownloadreqGrid');
      $this->setDefaultSort('productdownloadreq_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('productdownloadreq/productdownloadreq')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('productdownloadreq_id', array(
          'header'    => Mage::helper('productdownloadreq')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'productdownloadreq_id',
      ));

      $this->addColumn('activity', array(
          'header'    => Mage::helper('productdownloadreq')->__('Activity'),
          'align'     =>'left',
		  'width'     => '200px',
          'index'     => 'activity',
		  'type'      => 'options',
          'options'   => array(
		      1 => 'Full Product Download',
			  2 => 'Inventory Download',	
               )
      ));
	  $this->addColumn('vendorname',array(
	  		'header'	=>Mage::helper('productdownloadreq')->__('Vendor Name'),
			'align'		=>'left',
			'width'     => '200px',
			'index'		=>'vendorname',
			'type' 		=> 'options',
	        'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
    	    'filter' => 'udropship/vendor_gridColumnFilter'
	  ));
	  $this->addColumn('status', array(
          'header'    => Mage::helper('productdownloadreq')->__('Status'),
          'align'     => 'left',
          'width'     => '200px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Requested',
              2 => 'Completed',
			 
          ),
      ));
	   $this->addColumn('csvdownload', array(
          'header'    => Mage::helper('productdownloadreq')->__('Csv'),
          'align'     => 'left',
          'width'     => '200px',
          'index'     => 'csvdownload',
          'type'      => 'text',
         
      ));
	  
	  $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('productdownloadreq')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('productdownloadreq')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('productdownloadreq')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('productdownloadreq')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productdownloadreq_id');
        $this->getMassactionBlock()->setFormFieldName('productdownloadreq');
		
		 $this->getMassactionBlock()->addItem('generate', array(
             'label'    => Mage::helper('productdownloadreq')->__('Generate'),
             'url'      => $this->getUrl('*/*/generate'),
               ));
		

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productdownloadreq')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productdownloadreq')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('productdownloadreq/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('productdownloadreq')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('productdownloadreq')->__('Status'),
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
