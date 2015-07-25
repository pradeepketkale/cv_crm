<?php
class Craftsvilla_Shipmentpayoutlite_Block_Adminhtml_Shipmentpayoutlite extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_shipmentpayoutlite';
    $this->_blockGroup = 'shipmentpayoutlite';
    $this->_headerText = Mage::helper('shipmentpayoutlite')->__('Shipment Payout Lite');
    $this->_addButtonLabel = Mage::helper('shipmentpayoutlite')->__('Add Item');
    parent::__construct();
    $this->_removeButton('add');	
  }
}
