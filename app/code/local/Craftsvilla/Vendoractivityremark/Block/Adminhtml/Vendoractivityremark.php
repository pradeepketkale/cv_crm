<?php
class Craftsvilla_Vendoractivityremark_Block_Adminhtml_Vendoractivityremark extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_vendoractivityremark';
    $this->_blockGroup = 'vendoractivityremark';
    $this->_headerText = Mage::helper('vendoractivityremark')->__('Vendor Activity Remark');
    $this->_addButtonLabel = Mage::helper('vendoractivityremark')->__('Add Item');
    parent::__construct();
    $this->_removeButton('add');
  }
}
