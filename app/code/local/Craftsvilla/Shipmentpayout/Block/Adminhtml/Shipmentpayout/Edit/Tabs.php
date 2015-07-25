<?php

class Craftsvilla_Shipmentpayout_Block_Adminhtml_Shipmentpayout_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('Shipmentpayout_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shipmentpayout')->__('Shipmentpayout Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shipmentpayout')->__('Shipmentpayout Information'),
          'title'     => Mage::helper('shipmentpayout')->__('Shipmentpayout Information'),
          'content'   => $this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
