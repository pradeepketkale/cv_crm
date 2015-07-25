<?php
class Craftsvilla_Managemkt_Block_Adminhtml_Managemkt extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_managemkt';
    $this->_blockGroup = 'managemkt';
    $this->_headerText = Mage::helper('managemkt')->__('Manage Marketing');
    $this->_addButtonLabel = Mage::helper('managemkt')->__('Add MNGMKT');
    parent::__construct();
  }
}