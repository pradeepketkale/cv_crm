<?php
class Craftsvilla_Vendorneftcode_Block_Adminhtml_Vendorneftcode extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_vendorneftcode';
    $this->_blockGroup = 'vendorneftcode';
    $this->_headerText = Mage::helper('vendorneftcode')->__('Vendor NEFT Code');
    $this->_addButtonLabel = Mage::helper('vendorneftcode')->__('Add Neftcode');
    parent::__construct();
  }
}
