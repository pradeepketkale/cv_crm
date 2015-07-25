<?php
class Craftsvilla_Codshipments_Block_Adminhtml_Codshipments extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_codshipments';
    $this->_blockGroup = 'codshipments';
    $this->_headerText = Mage::helper('codshipments')->__('Cod Shipments');
  //  $this->_addButtonLabel = Mage::helper('codshipments')->__('Add Item');
    parent::__construct();
  }
}