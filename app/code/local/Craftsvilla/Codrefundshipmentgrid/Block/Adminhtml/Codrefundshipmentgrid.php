<?php
class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentgrid extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_codrefundshipmentgrid';
    $this->_blockGroup = 'codrefundshipmentgrid';
    $this->_headerText = Mage::helper('codrefundshipmentgrid')->__('Refund Cod Shipment');
    $this->_addButtonLabel = Mage::helper('codrefundshipmentgrid')->__('Add Refundcodshipment');
    parent::__construct();
  }
}
