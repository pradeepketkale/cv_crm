<?php

class Craftsvilla_Shipmentpayoutlite_Block_Adminhtml_Shipmentpayoutlite_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shipmentpayoutlite_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shipmentpayoutlite')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shipmentpayoutlite')->__('Item Information'),
          'title'     => Mage::helper('shipmentpayoutlite')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('shipmentpayoutlite/adminhtml_shipmentpayoutlite_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
