<?php

class Craftsvilla_Activeshipment_Block_Adminhtml_Activeshipment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('activeshipment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('activeshipment')->__('Active Shipment Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('activeshipment')->__('Active Shipment Information'),
          'title'     => Mage::helper('activeshipment')->__('Active Shipment Information'),
          'content'   => $this->getLayout()->createBlock('activeshipment/adminhtml_activeshipment_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}