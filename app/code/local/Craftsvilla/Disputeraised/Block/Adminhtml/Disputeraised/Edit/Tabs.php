<?php

class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('disputeraised_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('disputeraised')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('disputeraised')->__('Item Information'),
          'title'     => Mage::helper('disputeraised')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('disputeraised/adminhtml_disputeraised_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}