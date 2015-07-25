<?php

class Craftsvilla_Managemkt_Block_Adminhtml_Managemkt_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('managemktGrid');
      $this->setDefaultSort('managemkt_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('managemkt/managemkt')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('managemkt_id', array(
          'header'    => Mage::helper('managemkt')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'managemkt_id',
      ));

      $this->addColumn('activity', array(
          'header'    => Mage::helper('managemkt')->__('Activity'),
          'align'     =>'left',
          'index'     => 'activity',
		  'type'      => 'options',
          'options'   => array(
		  	  1 => 'Facebook Post',
			  2 => 'Emailer',	
              3 => 'Homepage Banner',
              4 => 'Homepage Products',
			  5 => 'Featured Seller',
			  6 => 'Guaranteed Sale',
			  )
      ));
	  $this->addColumn('vendorname',array(
	  		'header'	=>Mage::helper('managemkt')->__('Vendor Name'),
			'align'		=>'left',
			'index'		=>'vendorname',
			'type' 		=> 'options',
	        'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
    	    'filter' => 'udropship/vendor_gridColumnFilter'
	  ));
	  $this->addColumn('cost',array(
	  		'header'	=>Mage::helper('managemkt')->__('Cost'),
			'align'		=>'left',
			'index'		=> 'cost',
			'type'  	  => 'price',
	        'currency'  => 'base_currency_code',
	        'currency_code' => Mage::getStoreConfig('currency/options/base'),
	  ));
      $this->addColumn('status', array(
          'header'    => Mage::helper('managemkt')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Requested',
              2 => 'Accepted',
			  3 => 'Executed',
			  4 => 'On Hold',
			  5 => 'Declined',
			  6 => 'Cancelled'
          ),
      ));
	  
	  $this->addColumn('start_date', array(
          'header'    => Mage::helper('managemkt')->__('Start Date'),
          'align'     => 'left',
          'index'     => 'start_date',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));
	  $this->addColumn('end_date', array(
          'header'    => Mage::helper('managemkt')->__('End Date'),
          'align'     => 'left',
          'index'     => 'end_date',
		  'format' 	  => 'yyyy-MM-dd',
		  'type'      => 'datetime',
      ));
	  $this->addColumn('comment_url',array(
	  	'header'	=> Mage::helper('managemkt')->__('Comment/Product_url'),
		'align'		=> 'left',
		'index'		=> 'comment_url',
	  ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('managemkt')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('managemkt')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
//		$this->addExportType('*/*/exportCsv', Mage::helper('managemkt')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('managemkt')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('managemkt_id');
        $this->getMassactionBlock()->setFormFieldName('managemkt');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('managemkt')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('managemkt')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('managemkt/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('managemkt')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('managemkt')->__('Status'),
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
