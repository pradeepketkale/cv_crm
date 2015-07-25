<?php
class Craftsvilla_Ebslink_Block_Adminhtml_Ebslink extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_ebslink';
    $this->_blockGroup = 'ebslink';
    $this->_headerText = Mage::helper('ebslink')->__('Ebs Link Details');
    $this->_addButtonLabel = Mage::helper('ebslink')->__('Add EbsLink');
	parent::__construct();
  }
}