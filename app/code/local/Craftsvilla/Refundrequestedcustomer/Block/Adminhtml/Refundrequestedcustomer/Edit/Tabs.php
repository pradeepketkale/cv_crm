<?php

class Craftsvilla_Refundrequestedcustomer_Block_Adminhtml_Refundrequestedcustomer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('refundrequestedcustomer_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('refundrequestedcustomer')->__('Refund Request Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('Refund Request Information'),
          'title'     => Mage::helper('refundrequestedcustomer')->__('Refund Request Information'),
          'content'   => $this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
