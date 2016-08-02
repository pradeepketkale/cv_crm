<?php

class Craftsvilla_Customerlite_Block_Adminhtml_Customerlite_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('customerlite_form', array('legend'=>Mage::helper('customerlite')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('customerlite')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('customerlite')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('customerlite')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('customerlite')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('customerlite')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('customerlite')->__('Content'),
          'title'     => Mage::helper('customerlite')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getCustomerliteData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomerData());
          Mage::getSingleton('adminhtml/session')->setCustomerliteData(null);
      } elseif ( Mage::registry('customerlite_data') ) {
          $form->setValues(Mage::registry('customerlite_data')->getData());
      }
      return parent::_prepareForm();
  }
}
