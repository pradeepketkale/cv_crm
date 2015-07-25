<?php
class Craftsvilla_Productdownloadreq_Block_Adminhtml_Productdownloadreq extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_productdownloadreq';
    $this->_blockGroup = 'productdownloadreq';
    $this->_headerText = Mage::helper('productdownloadreq')->__('Manage Request');
    $this->_addButtonLabel = Mage::helper('productdownloadreq')->__('Add Request');
    parent::__construct();
  }
}