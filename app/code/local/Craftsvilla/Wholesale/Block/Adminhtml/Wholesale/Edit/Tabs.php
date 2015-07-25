<?php

class Craftsvilla_Wholesale_Block_Adminhtml_Wholesale_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('wholesale_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('wholesale')->__('Cust_Inquiry Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('wholesale')->__('Cust_Inquiry Information'),
          'title'     => Mage::helper('wholesale')->__('Cust_Inquiry Information'),
          'content'   => $this->getLayout()->createBlock('wholesale/adminhtml_wholesale_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}