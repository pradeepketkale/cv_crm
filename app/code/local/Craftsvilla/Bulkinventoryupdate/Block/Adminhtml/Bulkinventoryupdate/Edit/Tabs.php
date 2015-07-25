<?php

class Craftsvilla_Bulkinventoryupdate_Block_Adminhtml_Bulkinventoryupdate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('bulkinventoryupdate_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('bulkinventoryupdate')->__('Csv Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('Csv Information'),
          'title'     => Mage::helper('bulkinventoryupdate')->__('Csv Information'),
          'content'   => $this->getLayout()->createBlock('bulkinventoryupdate/adminhtml_bulkinventoryupdate_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}