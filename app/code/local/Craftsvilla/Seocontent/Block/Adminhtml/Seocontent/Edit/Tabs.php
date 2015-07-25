<?php

class Craftsvilla_Seocontent_Block_Adminhtml_Seocontent_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('seocontent_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('seocontent')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('seocontent')->__('Item Information'),
          'title'     => Mage::helper('seocontent')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('seocontent/adminhtml_seocontent_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}