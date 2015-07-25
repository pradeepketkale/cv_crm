<?php

class Craftsvilla_Couponrequest_Block_Adminhtml_Couponrequest_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('couponrequest_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('couponrequest')->__('Coupon Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('couponrequest')->__('Coupon Information'),
          'title'     => Mage::helper('couponrequest')->__('Coupon Information'),
          'content'   => $this->getLayout()->createBlock('couponrequest/adminhtml_couponrequest_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
