<?php
class Craftsvilla_Activeshipment_Block_Adminhtml_Activeshipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_activeshipment';
    $this->_blockGroup = 'activeshipment';
    $this->_headerText = Mage::helper('activeshipment')->__('Active Shipment');
    $this->_addButtonLabel = Mage::helper('activeshipment')->__('Add Active Shipment');
    parent::__construct();
  }
}