<?php

class Craftsvilla_Productdownloadreq_Block_Adminhtml_Productdownloadreq_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('productdownloadreq_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('productdownloadreq')->__('Productdownloadreq Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('productdownloadreq')->__('Productdownloadreq Information'),
          'title'     => Mage::helper('productdownloadreq')->__('Productdownloadreq Information'),
          'content'   => $this->getLayout()->createBlock('productdownloadreq/adminhtml_productdownloadreq_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}