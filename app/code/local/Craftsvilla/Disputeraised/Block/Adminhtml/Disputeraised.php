<?php
class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_disputeraised';
    $this->_blockGroup = 'disputeraised';
    $this->_headerText = Mage::helper('disputeraised')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('disputeraised')->__('Add Item');
    parent::__construct();
  }
}