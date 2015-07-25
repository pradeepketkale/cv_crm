<?php
class Craftsvilla_Sellerqualitycraftsvilla_Block_Adminhtml_Sellerqualitycraftsvilla extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_sellerqualitycraftsvilla';
    $this->_blockGroup = 'sellerqualitycraftsvilla';
    $this->_headerText = Mage::helper('sellerqualitycraftsvilla')->__('Seller Quality Craftsvilla');
    $this->_addButtonLabel = Mage::helper('sellerqualitycraftsvilla')->__('Add Seller');
    parent::__construct();
    $this->_removeButton('add'); // To Remove AddNew Button
  }
}
