<?php
class Craftsvilla_Couponrequest_Block_Adminhtml_Couponrequest extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_couponrequest';
    $this->_blockGroup = 'couponrequest';
    $this->_headerText = Mage::helper('couponrequest')->__('Coupon Request');
    $this->_addButtonLabel = Mage::helper('couponrequest')->__('Add Coupon');
    parent::__construct();
  }
}
