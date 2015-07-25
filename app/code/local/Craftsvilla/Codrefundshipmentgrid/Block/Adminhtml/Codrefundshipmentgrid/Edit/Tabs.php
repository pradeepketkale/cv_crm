<?php

class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentgrid_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('codrefundshipmentgrid_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('codrefundshipmentgrid')->__('Shipment Inf'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Shipment Inf'),
          'title'     => Mage::helper('codrefundshipmentgrid')->__('Shipment Inf'),
          'content'   => $this->getLayout()->createBlock('codrefundshipmentgrid/adminhtml_codrefundshipmentgrid_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
