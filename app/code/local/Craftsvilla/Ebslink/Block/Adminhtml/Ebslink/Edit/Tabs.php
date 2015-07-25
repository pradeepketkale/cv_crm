<?php

class Craftsvilla_Ebslink_Block_Adminhtml_Ebslink_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('ebslink_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('ebslink')->__('Ebslink Details'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('ebslink')->__('Ebslink Details'),
          'title'     => Mage::helper('ebslink')->__('Ebslink Details'),
          'content'   => $this->getLayout()->createBlock('ebslink/adminhtml_ebslink_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}