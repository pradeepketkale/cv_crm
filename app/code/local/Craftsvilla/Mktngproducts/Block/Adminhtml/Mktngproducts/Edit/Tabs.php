<?php

class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('mktngproducts_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('mktngproducts')->__('Product Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('mktngproducts')->__('Product Information'),
          'title'     => Mage::helper('mktngproducts')->__('Product Information'),
          'content'   => $this->getLayout()->createBlock('mktngproducts/adminhtml_mktngproducts_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
