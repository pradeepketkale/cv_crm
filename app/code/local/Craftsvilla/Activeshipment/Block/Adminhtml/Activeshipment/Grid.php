<?php

class Craftsvilla_Activeshipment_Block_Adminhtml_Activeshipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('activeshipmentGrid');
      $this->setDefaultSort('activeshipment_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('activeshipment/activeshipment')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('activeshipment_id', array(
          'header'    => Mage::helper('activeshipment')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'activeshipment_id',
      ));

      $this->addColumn('shipment_id', array(
          'header'    => Mage::helper('activeshipment')->__('Shipment Id'),
          'align'     =>'left',
          'index'     => 'shipment_id',
      ));
	$this->addColumn('cust_status', array(
		  'header'    => Mage::helper('activeshipment')->__('Cust Status'),
		  'align'     =>'left',
		  'index'     => 'cust_status',
	  ));
	$this->addColumn('primary_category', array(
			  'header'    => Mage::helper('activeshipment')->__('Primary Category'),
			  'align'     =>'left',
			  'index'     => 'primary_category',
		  ));
	$this->addColumn('expected_shipingdate', array(
          'header'    => Mage::helper('activeshipment')->__('Expected Shiping Date'),
          'align'     =>'left',
          'index'     =>'expected_shipingdate',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));	  
	$this->addColumn('vendor_claimedfrom', array(
			  'header'    => Mage::helper('activeshipment')->__('Vendor Claim From'),
			  'align'     =>'left',
			  'index'     => 'vendor_claimedfrom',
			   'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
               'filter' => 'udropship/vendor_gridColumnFilter'
		  ));
	$this->addColumn('vendor_claimedto', array(
			  'header'    => Mage::helper('activeshipment')->__('Vendor Claim To'),
			  'align'     =>'left',
			  'index'     => 'vendor_claimedto',
			  'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
              'filter' => 'udropship/vendor_gridColumnFilter'
		  ));
	$this->addColumn('claimed_date', array(
          'header'    => Mage::helper('activeshipment')->__('Claimed Date'),
          'align'     =>'left',
          'index'     =>'claimed_date',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));
	 $this->addColumn('created_time', array(
          'header'    => Mage::helper('activeshipment')->__('Created Date'),
          'align'     =>'left',
          'index'     =>'created_time',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));
	  $this->addColumn('update_time', array(
          'header'    => Mage::helper('activeshipment')->__('Updated Date'),
          'align'     =>'left',
          'index'     =>'update_time',
          'type'    =>'datetime',
          //'renderer' = new Namespace_Module_Block_Adminhtml_Renderer_Date(),
      ));

     /* $this->addColumn('status', array(
          'header'    => Mage::helper('activeshipment')->__('Status'),
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
               // 'header'    =>  Mage::helper('activeshipment')->__('Action'),
               // 'width'     => '100',
               // 'type'      => 'action',
               // 'getter'    => 'getId',
               // 'actions'   => array(
               //     array(
               //         'caption'   => Mage::helper('activeshipment')->__('Edit'),
               //         'url'       => array('base'=> '*/*/edit'),
              ////         'field'     => 'id'
              //      )
              //  ),
               // 'filter'    => false,
               // 'sortable'  => false,
               // 'index'     => 'stores',
               // 'is_system' => true,
        //));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('activeshipment')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('activeshipment')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('activeshipment_id');
        $this->getMassactionBlock()->setFormFieldName('activeshipment');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('activeshipment')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('activeshipment')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('activeshipment/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('activeshipment')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('activeshipment')->__('Status'),
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