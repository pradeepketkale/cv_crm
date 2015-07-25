<?php

class Craftsvilla_Homepage_Block_Adminhtml_Homepage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('homepage_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('homepage')->__('Product Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('homepage')->__('Product Information'),
          'title'     => Mage::helper('homepage')->__('Product Information'),
          'content'   => $this->getLayout()->createBlock('homepage/adminhtml_homepage_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}