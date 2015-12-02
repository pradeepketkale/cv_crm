<?php
class Craftsvilla_Productmanagement_Block_Adminhtml_Productmanagement extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productmanagement';
    $this->_blockGroup = 'productmanagement';
    $this->_headerText = Mage::helper('productmanagement')->__('Product Management');
     $this->_addButtonLabel = Mage::helper('productmanagement')->__('');

    parent::__construct();

	
  }
}
