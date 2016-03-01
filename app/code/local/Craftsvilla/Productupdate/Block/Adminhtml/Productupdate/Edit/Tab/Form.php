<?php

class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('productupdate_form', array('legend'=>Mage::helper('productupdate')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('productupdate')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('productupdate')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('productupdate')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('productupdate')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('productupdate')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('productupdate')->__('Content'),
          'title'     => Mage::helper('productupdate')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
      /*$fieldset->addType('add_button', 'Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Edit_Tab_Field_Custom'); 
      $fieldset->addField('productupdate_add_button', 'add_button', array(
         'title' => Mage::helper('productupdate')->__('My Button Name'),
         'id' => 'buttonadder_id',
         'class' => 'buttonadder_class',
         'style' => 'color:white;height:50px',  //just an example
         'onclick' => 'addbutton.add(this)',
         'type' => 'button',                                       
    ));*/
    
      if ( Mage::getSingleton('adminhtml/session')->getProductupdateData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getProductupdateData());
          Mage::getSingleton('adminhtml/session')->setProductupdateData(null);
      } elseif ( Mage::registry('productupdate_data') ) {
          $form->setValues(Mage::registry('productupdate_data')->getData());
      }
      return parent::_prepareForm();
  }
}
