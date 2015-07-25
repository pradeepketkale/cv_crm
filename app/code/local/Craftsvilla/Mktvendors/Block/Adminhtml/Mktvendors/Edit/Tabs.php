<?php

class Craftsvilla_Mktvendors_Block_Adminhtml_Mktvendors_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('mktvendors_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('mktvendors')->__('Mktpk Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('mktvendors')->__('Mktpk Information'),
          'title'     => Mage::helper('mktvendors')->__('Mktpk Information'),
          'content'   => $this->getLayout()->createBlock('mktvendors/adminhtml_mktvendors_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}