<?php

class Craftsvilla_Sellerqualitycraftsvilla_Block_Adminhtml_Sellerqualitycraftsvilla_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('sellerqualitycraftsvilla_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('sellerqualitycraftsvilla')->__('Seller Quality Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('Seller Quality Information'),
          'title'     => Mage::helper('sellerqualitycraftsvilla')->__('Seller Quality Information'),
          'content'   => $this->getLayout()->createBlock('sellerqualitycraftsvilla/adminhtml_sellerqualitycraftsvilla_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
