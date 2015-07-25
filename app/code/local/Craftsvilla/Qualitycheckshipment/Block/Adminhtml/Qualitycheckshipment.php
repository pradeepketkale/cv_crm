<?php
class Craftsvilla_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_qualitycheckshipment';
    $this->_blockGroup = 'qualitycheckshipment';
    $this->_headerText = Mage::helper('qualitycheckshipment')->__('QA Check Shipments');
    $this->_addButtonLabel = Mage::helper('qualitycheckshipment')->__('Add Item');
	parent::__construct();
    $this->_removeButton('add');
  }
}
