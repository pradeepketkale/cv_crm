<?php

class Craftsvilla_Craftsvillapickupreference_Block_Adminhtml_Craftsvillapickupreference_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('craftsvillapickupreference_form', array('legend'=>Mage::helper('craftsvillapickupreference')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('craftsvillapickupreference')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('craftsvillapickupreference')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('craftsvillapickupreference')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('craftsvillapickupreference')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('craftsvillapickupreference')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('craftsvillapickupreference')->__('Content'),
          'title'     => Mage::helper('craftsvillapickupreference')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getCraftsvillapickupreferenceData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCraftsvillapickupreferenceData());
          Mage::getSingleton('adminhtml/session')->setCraftsvillapickupreferenceData(null);
      } elseif ( Mage::registry('craftsvillapickupreference_data') ) {
          $form->setValues(Mage::registry('craftsvillapickupreference_data')->getData());
      }
      return parent::_prepareForm();
  }
}
