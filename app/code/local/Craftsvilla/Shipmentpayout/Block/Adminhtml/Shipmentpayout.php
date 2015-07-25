<?php
class Craftsvilla_Shipmentpayout_Block_Adminhtml_Shipmentpayout extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_shipmentpayout';
    $this->_blockGroup = 'shipmentpayout';
    $this->_headerText = Mage::helper('shipmentpayout')->__('Shipmentpayout Manager');
    $this->_addButtonLabel = Mage::helper('shipmentpayout')->__('Add Shipmentpayout');
    parent::__construct();
  }
}
