<?php
class Craftsvilla_Utrreport_Block_Adminhtml_Utrreport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_utrreport';
    $this->_blockGroup = 'utrreport';
    $this->_headerText = Mage::helper('utrreport')->__('UTR Details');
    $this->_addButtonLabel = Mage::helper('utrreport')->__('Add UTR');
    parent::__construct();
  }
}