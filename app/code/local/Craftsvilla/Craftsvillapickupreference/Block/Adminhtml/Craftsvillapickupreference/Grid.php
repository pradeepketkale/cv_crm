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
	$this->setUseAjax(true);	
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

      //$this->addColumn('created_date', array(
         // 'header'    => Mage::helper('craftsvillapickupreference')->__('Created Date'),
         // 'align'     =>'left',
        //  'index'     => 'created_date',
      //));

      $this->addColumn('created_date', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Created Date'),
          'align'     =>'left',
          'index'     => 'created_date',
          'filter_index' => 'main_table.created_date',
	  'type' 	  => 'datetime',
      ));

     $this->addColumn('Vendor_id', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Vendor Id'),
          'align'     =>'left',
          'index'     => 'Vendor_id',
      ));

     $this->addColumn('vendor_name', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Vendor Name'),
          'align'     =>'left',
          'index'     => 'vendor_name',
	  
      ));

      $this->addColumn('courier_name', array(
          'header'    => Mage::helper('craftsvillapickupreference')->__('Courier Name'),
          'align'     =>'left',
          'index'     => 'courier_name',
      ));
  
      return parent::_prepareColumns();
  }

   

  //public function getRowUrl($row)
  //{
   //   return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  //}
    public function getRowUrl($row)
  {
     // return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  public function getGridUrl()
 	{
          return $this->getUrl('*/*/grid', array('_current' => true));
  	}
}
