<?php

class Craftsvilla_Customerlite_Block_Adminhtml_Customerlite_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('customerlite_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerlite')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('customerlite')->__('Item Information'),
          'title'     => Mage::helper('customerlite')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('customerlite/adminhtml_customerlite_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
