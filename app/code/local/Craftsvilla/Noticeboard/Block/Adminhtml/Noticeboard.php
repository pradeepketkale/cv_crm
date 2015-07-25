<?php
class Craftsvilla_Noticeboard_Block_Adminhtml_Noticeboard extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_noticeboard';
    $this->_blockGroup = 'noticeboard';
    $this->_headerText = Mage::helper('noticeboard')->__('Noticeboard');
    $this->_addButtonLabel = Mage::helper('noticeboard')->__('Add Notice');
    parent::__construct();
	
	
  }
}