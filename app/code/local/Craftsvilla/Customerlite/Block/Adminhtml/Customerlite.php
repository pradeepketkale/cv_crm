<?php
class Craftsvilla_Customerlite_Block_Adminhtml_Customerlite extends Mage_Adminhtml_Block_Widget_Grid_Container
{
 /* public function __construct()
  {
    $this->_controller = 'adminhtml_customerlite';
    $this->_blockGroup = 'customerlite';
    $this->_headerText = Mage::helper('customerlite')->__('Customer Lite');
    $this->_addButtonLabel = Mage::helper('customer')->__('Add New Customer');
    parent::__construct();
    $this->_removeButton('add');	
  }*/
public function __construct()
    {
        $this->_controller = 'adminhtml_customerlite';
	$this->_blockGroup = 'customerlite';
        $this->_headerText = Mage::helper('customerlite')->__('Customer Lite');
        $this->_addButtonLabel = Mage::helper('customerlite')->__('Add New Customer');
        parent::__construct();
    }
}
