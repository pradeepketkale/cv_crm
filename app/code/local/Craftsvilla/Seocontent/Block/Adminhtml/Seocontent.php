<?php
class Craftsvilla_Seocontent_Block_Adminhtml_Seocontent extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_seocontent';
    $this->_blockGroup = 'seocontent';
    $this->_headerText = Mage::helper('seocontent')->__('Content Manager');
    $this->_addButtonLabel = Mage::helper('seocontent')->__('Add Content');
    parent::__construct();
  }
}