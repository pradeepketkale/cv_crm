<?php
class Craftsvilla_Wholesale_Block_Adminhtml_Wholesale extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_wholesale';
    $this->_blockGroup = 'wholesale';
    $this->_headerText = Mage::helper('wholesale')->__('Wholesale Details');
   // Remove the Add Item Button
    $this->_addButtonLabel = Mage::helper('wholesale')->__('Add Item');
	parent::__construct();
	//$this->_removeButton('add');
  }
}