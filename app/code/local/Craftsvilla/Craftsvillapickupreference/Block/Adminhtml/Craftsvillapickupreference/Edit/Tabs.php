<?php

class Craftsvilla_Craftsvillapickupreference_Block_Adminhtml_Craftsvillapickupreference_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('craftsvillapickupreference_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('craftsvillapickupreference')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('craftsvillapickupreference')->__('Item Information'),
          'title'     => Mage::helper('craftsvillapickupreference')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('craftsvillapickupreference/adminhtml_craftsvillapickupreference_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
