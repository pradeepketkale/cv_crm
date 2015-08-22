<?php
class Craftsvilla_Craftsvillapickupreference_Block_Adminhtml_Craftsvillapickupreference extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_craftsvillapickupreference';
    $this->_blockGroup = 'craftsvillapickupreference';
    $this->_headerText = Mage::helper('craftsvillapickupreference')->__('Craftsvilla Pickup Reference');
    $this->_addButtonLabel = Mage::helper('craftsvillapickupreference')->__('Add Item');
    parent::__construct();
    $this->_removeButton('add');
  }
}
