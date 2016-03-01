<?php

class Craftsvilla_Productmanagement_Block_Adminhtml_Productmanagement_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('productmanagementGrid');
      $this->setDefaultSort('productmanagement_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }
  protected function _prepareCollection()
  {
      //$collection = Mage::getModel('productmanagement/productmanagement')->getCollection();
      //$this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    //echo  __METHOD__ . " (Line #" . __LINE__ . ")"; 
      
      $hlp = Mage::helper('productmanagement');
      $hlp->searchBox();
    
      //addFieldToFilter($attribute, $condition=null);
	
      //return parent::_prepareColumns();
  }
 
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('productmanagement_id');
        $this->getMassactionBlock()->setFormFieldName('productmanagement');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('productmanagement')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('productmanagement')->__('Are you sure?')
        ));
		$this->getMassactionBlock()->addItem('promo', array(
             'label'    => Mage::helper('productmanagement')->__('Promote'),
             'url'      => $this->getUrl('*/*/massPromote'),
       ));
		$this->getMassactionBlock()->addItem('depromo', array(
             'label'    => Mage::helper('productmanagement')->__('DePromote'),
             'url'      => $this->getUrl('*/*/massDepromote'),
       ));

  
        return $this;
    }

  public function getRowUrl($row)
  {
    //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  public function getGridUrl()
  {
          return $this->getUrl('*/*/grid', array('_current' => true));
  }

}
