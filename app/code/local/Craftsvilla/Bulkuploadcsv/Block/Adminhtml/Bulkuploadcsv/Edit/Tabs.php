<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('bulkuploadcsv_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('bulkuploadcsv')->__('Csv Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Csv Information'),
          'title'     => Mage::helper('bulkuploadcsv')->__('Csv Information'),
          'content'   => $this->getLayout()->createBlock('bulkuploadcsv/adminhtml_bulkuploadcsv_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}