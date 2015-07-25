<?php

class Craftsvilla_Shipmentlite_Block_Adminhtml_Shipmentlite_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shipmentlite_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shipmentlite')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shipmentlite')->__('Item Information'),
          'title'     => Mage::helper('shipmentlite')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('shipmentlite/adminhtml_shipmentlite_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
