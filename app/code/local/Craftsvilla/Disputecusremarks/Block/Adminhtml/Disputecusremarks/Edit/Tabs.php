<?php

class Craftsvilla_Disputecusremarks_Block_Adminhtml_Disputecusremarks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('disputecusremarks_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('disputecusremarks')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('disputecusremarks')->__('Item Information'),
          'title'     => Mage::helper('disputecusremarks')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('disputecusremarks/adminhtml_disputecusremarks_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
