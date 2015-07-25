<?php

class Craftsvilla_Customerreturn_Block_Adminhtml_Customerreturn_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('customerreturn_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerreturn')->__('customer return data'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('customerreturn')->__('Item Information'),
          'title'     => Mage::helper('customerreturn')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('customerreturn/adminhtml_customerreturn_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
