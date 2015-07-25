<?php

class Company_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('qualitycheckshipment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('qualitycheckshipment')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('qualitycheckshipment')->__('Item Information'),
          'title'     => Mage::helper('qualitycheckshipment')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('qualitycheckshipment/adminhtml_qualitycheckshipment_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
