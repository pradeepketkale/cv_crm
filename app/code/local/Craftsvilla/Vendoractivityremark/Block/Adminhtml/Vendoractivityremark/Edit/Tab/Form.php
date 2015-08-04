<?php

class Craftsvilla_Vendoractivityremark_Block_Adminhtml_Vendoractivityremark_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('vendoractivityremark_form', array('legend'=>Mage::helper('vendoractivityremark')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('vendoractivityremark')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('vendoractivityremark')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('vendoractivityremark')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('vendoractivityremark')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('vendoractivityremark')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('vendoractivityremark')->__('Content'),
          'title'     => Mage::helper('vendoractivityremark')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getVendoractivityremarkData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getVendoractivityremarkData());
          Mage::getSingleton('adminhtml/session')->setVendoractivityremarkData(null);
      } elseif ( Mage::registry('vendoractivityremark_data') ) {
          $form->setValues(Mage::registry('vendoractivityremark_data')->getData());
      }
      return parent::_prepareForm();
  }
}
