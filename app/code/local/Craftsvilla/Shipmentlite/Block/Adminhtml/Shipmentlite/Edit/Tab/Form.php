<?php

class Craftsvilla_Shipmentlite_Block_Adminhtml_Shipmentlite_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shipmentlite_form', array('legend'=>Mage::helper('shipmentlite')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('shipmentlite')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('shipmentlite')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('shipmentlite')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shipmentlite')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('shipmentlite')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('shipmentlite')->__('Content'),
          'title'     => Mage::helper('shipmentlite')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getShipmentliteData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShipmentliteData());
          Mage::getSingleton('adminhtml/session')->setShipmentliteData(null);
      } elseif ( Mage::registry('shipmentlite_data') ) {
          $form->setValues(Mage::registry('shipmentlite_data')->getData());
      }
      return parent::_prepareForm();
  }
}
