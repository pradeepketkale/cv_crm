<?php

class Craftsvilla_Agentpayout_Block_Adminhtml_Agentpayout_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('agentpayout_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('agentpayout')->__('Payout Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('agentpayout')->__('Payout Information'),
          'title'     => Mage::helper('agentpayout')->__('Payout Information'),
          'content'   => $this->getLayout()->createBlock('agentpayout/adminhtml_agentpayout_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}