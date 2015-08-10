<?php

class Craftsvilla_Disputecusremarks_Block_Adminhtml_Disputecusremarks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('disputecusremarksGrid');
      $this->setDefaultSort('disputecusremarks_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('disputecusremarks/disputecusremarks')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('disputecusremarks_id', array(
          'header'    => Mage::helper('disputecusremarks')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'disputecusremarks_id',
      ));
	$this->addColumn('shipment_id', array(
          'header'    => Mage::helper('disputecusremarks')->__('shipment ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'shipment_id',
      ));
      
	$this->addColumn('vendor_id', array(
          'header'    => Mage::helper('disputecusremarks')->__('Vendor ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vendor_id',
      ));
    $this->addColumn('vendor_name', array(
          'header'    => Mage::helper('disputecusremarks')->__('Vendor Name'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'vendor_name',
      ));
	$this->addColumn('remarks', array(
          'header'    => Mage::helper('disputecusremarks')->__('Remarks'),
          'align'     =>'right',
          'width'     => '200px',
          'index'     => 'remarks',
      ));
    
      

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*$this->addColumn('status', array(
          'header'    => Mage::helper('disputecusremarks')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));*/
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('disputecusremarks')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('disputecusremarks')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('disputecusremarks_id');
        $this->getMassactionBlock()->setFormFieldName('disputecusremarks');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('disputecusremarks')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('disputecusremarks')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('disputecusremarks/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('disputecusremarks')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('disputecusremarks')->__('Status'),
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
