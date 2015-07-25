<?php

class Craftsvilla_Utrreport_Block_Adminhtml_Utrreport_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('utrreport_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('utrreport')->__('UTR Details'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('utrreport')->__('UTR Details'),
          'title'     => Mage::helper('utrreport')->__('UTR Details'),
          'content'   => $this->getLayout()->createBlock('utrreport/adminhtml_utrreport_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}