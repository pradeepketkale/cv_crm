<?php
class Craftsvilla_Mktvendors_Block_Adminhtml_Mktvendors extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_mktvendors';
    $this->_blockGroup = 'mktvendors';
    $this->_headerText = Mage::helper('mktvendors')->__('MktVendors Details');
    $this->_addButtonLabel = Mage::helper('mktvendors')->__('Add Mktpk');
    parent::__construct();
  }
}