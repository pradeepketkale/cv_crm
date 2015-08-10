<?php
class Craftsvilla_Disputecusremarks_Block_Adminhtml_Disputecusremarks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_disputecusremarks';
    $this->_blockGroup = 'disputecusremarks';
    $this->_headerText = Mage::helper('disputecusremarks')->__('Disputed Customer Remarks');
    $this->_addButtonLabel = Mage::helper('disputecusremarks')->__('Add Item');
    parent::__construct();
  }
}
