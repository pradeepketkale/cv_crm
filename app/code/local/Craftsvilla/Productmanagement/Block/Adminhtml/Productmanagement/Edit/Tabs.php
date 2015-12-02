<?php

class Craftsvilla_Productmanagement_Block_Adminhtml_Productmanagement_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('productmanagement_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('productmanagement')->__('Product Management'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('productmanagement')->__('Product Management'),
          'title'     => Mage::helper('productmanagement')->__('Product Management'),
          'content'   => $this->getLayout()->createBlock('productmanagement/adminhtml_productmanagement_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
