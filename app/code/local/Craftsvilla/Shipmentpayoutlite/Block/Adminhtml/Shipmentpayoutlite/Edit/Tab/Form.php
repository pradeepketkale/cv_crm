<?php

class Craftsvilla_Shipmentpayoutlite_Block_Adminhtml_Shipmentpayoutlite_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shipmentpayoutlite_form', array('legend'=>Mage::helper('shipmentpayoutlite')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('shipmentpayoutlite')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('shipmentpayoutlite')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('shipmentpayoutlite')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shipmentpayoutlite')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('shipmentpayoutlite')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('shipmentpayoutlite')->__('Content'),
          'title'     => Mage::helper('shipmentpayoutlite')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getShipmentpayoutliteData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShipmentpayoutliteData());
          Mage::getSingleton('adminhtml/session')->setShipmentpayoutliteData(null);
      } elseif ( Mage::registry('shipmentpayoutlite_data') ) {
          $form->setValues(Mage::registry('shipmentpayoutlite_data')->getData());
      }
      return parent::_prepareForm();
  }
}
