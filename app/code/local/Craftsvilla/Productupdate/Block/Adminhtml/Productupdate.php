<?php
class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productupdate';
    $this->_blockGroup = 'productupdate';
    $this->_headerText = Mage::helper('productupdate')->__('Item Manager');
   // $this->_addButtonLabel = Mage::helper('productupdate')->__('ReIndex');
    parent::__construct();
  }
}
