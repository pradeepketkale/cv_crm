<?php
class Craftsvilla_Bulkinventoryupdate_Block_Adminhtml_Bulkinventoryupdate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_bulkinventoryupdate';
    $this->_blockGroup = 'bulkinventoryupdate';
    $this->_headerText = Mage::helper('bulkinventoryupdate')->__('BulkInventoryUpdate');
    $this->_addButtonLabel = Mage::helper('bulkinventoryupdate')->__('Add Csv');
    parent::__construct();
  }
}