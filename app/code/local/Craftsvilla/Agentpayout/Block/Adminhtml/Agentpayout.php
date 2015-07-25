<?php
class Craftsvilla_Agentpayout_Block_Adminhtml_Agentpayout extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_agentpayout';
    $this->_blockGroup = 'agentpayout';
    $this->_headerText = Mage::helper('agentpayout')->__('Agentpayout Details');
    $this->_addButtonLabel = Mage::helper('agentpayout')->__('Add Payout');
    parent::__construct();
  }
}