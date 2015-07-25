<?php
class Craftsvilla_Homepage_Block_Adminhtml_Homepage2 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_homepage2';
    $this->_blockGroup = 'homepage';
    $this->_headerText = Mage::helper('homepage')->__('Homepage Products');
    $this->_addButtonLabel = Mage::helper('homepage')->__('Add Product');
    parent::__construct();
  }
}