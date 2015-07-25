<?php

class Craftsvilla_Noticeboard_Block_Adminhtml_Noticeboard_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('noticeboard_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('noticeboard')->__('Notice Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('noticeboard')->__('Notice Information'),
          'title'     => Mage::helper('noticeboard')->__('Notice Information'),
          'content'   => $this->getLayout()->createBlock('noticeboard/adminhtml_noticeboard_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}