<?php
class Craftsvilla_Customerreturn_Block_Adminhtml_Customerreturn extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_customerreturn';
    $this->_blockGroup = 'customerreturn';
    $this->_headerText = Mage::helper('customerreturn')->__('Customer Return Data');
    $this->_addButtonLabel = Mage::helper('customerreturn')->__('Add Return Shipment');
    parent::__construct();
  }
}
