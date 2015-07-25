<?php
class Craftsvilla_Uagent_Block_Adminhtml_Uagent extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_uagent';
    $this->_blockGroup = 'uagent';
    $this->_headerText = Mage::helper('uagent')->__('Agent Details');
    $this->_addButtonLabel = Mage::helper('uagent')->__('Add Agent');
    parent::__construct();
  }
}