<?php
class Craftsvilla_Shipmentlite_Block_Adminhtml_Shipmentlite extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_shipmentlite';
    $this->_blockGroup = 'shipmentlite';
    $this->_headerText = Mage::helper('shipmentlite')->__('ShipmentLite');
    $this->_addButtonLabel = Mage::helper('shipmentlite')->__('Add Item');
    parent::__construct();
    $this->_removeButton('add');
    
  }
}
