<?php

class Craftsvilla_Vendoractivityremark_Block_Adminhtml_Vendoractivityremark_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('vendoractivityremark_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('vendoractivityremark')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('vendoractivityremark')->__('Item Information'),
          'title'     => Mage::helper('vendoractivityremark')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('vendoractivityremark/adminhtml_vendoractivityremark_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
