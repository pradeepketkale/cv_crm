<?php

class Craftsvilla_Vendorneftcode_Block_Adminhtml_Vendorneftcode_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('vendorneftcode_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('vendorneftcode')->__('NEFT Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('vendorneftcode')->__('NEFT Information'),
          'title'     => Mage::helper('vendorneftcode')->__('NEFT Information'),
          'content'   => $this->getLayout()->createBlock('vendorneftcode/adminhtml_vendorneftcode_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
