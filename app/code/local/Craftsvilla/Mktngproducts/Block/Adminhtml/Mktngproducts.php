<?php
class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_mktngproducts';
    $this->_blockGroup = 'mktngproducts';
    $this->_headerText = Mage::helper('mktngproducts')->__('Marketing Products');
    $this->_addButtonLabel = Mage::helper('mktngproducts')->__('Add Marketing Product');
    parent::__construct();
  }
}
