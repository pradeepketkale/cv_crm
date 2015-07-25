<?php
class Craftsvilla_Refundrequestedcustomer_Block_Adminhtml_Refundrequestedcustomer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_refundrequestedcustomer';
    $this->_blockGroup = 'refundrequestedcustomer';
    $this->_headerText = Mage::helper('refundrequestedcustomer')->__('Refund Requested Customer');
   // $this->_addButtonLabel = Mage::helper('refundrequestedcustomer')->__('Add Refund Request');
    parent::__construct();
    $this->_removeButton('add'); // To Remove AddNew Button
  }
}
