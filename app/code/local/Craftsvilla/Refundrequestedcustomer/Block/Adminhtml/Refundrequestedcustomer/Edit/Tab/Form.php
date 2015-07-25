<?php

class Craftsvilla_Refundrequestedcustomer_Block_Adminhtml_Refundrequestedcustomer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('refundrequestedcustomer_form', array('legend'=>Mage::helper('refundrequestedcustomer')->__('Item information')));
     
      $fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('ShipmentID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));
	  $fieldset->addField('customer_name', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('CustomerName'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'customer_name',
      ));
      $fieldset->addField('account_number', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('AccountNumber'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'account_number',
      ));
      $fieldset->addField('name_on_account', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('NameOnAccount'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name_on_account',
      ));
      $fieldset->addField('ifsccode', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('IFSCcode'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ifsccode',
      ));
      $fieldset->addField('trackingcode', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('TrackingCode'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'trackingcode',
      ));
      $fieldset->addField('couriername', 'text', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('CourierName'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'couriername',
      ));
      
      
      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));*/
		
      /*$fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('refundrequestedcustomer')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('refundrequestedcustomer')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('refundrequestedcustomer')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('refundrequestedcustomer')->__('Content'),
          'title'     => Mage::helper('refundrequestedcustomer')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getRefundrequestedcustomerData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRefundrequestedcustomerData());
          Mage::getSingleton('adminhtml/session')->setRefundrequestedcustomerData(null);
      } elseif ( Mage::registry('refundrequestedcustomer_data') ) {
          $form->setValues(Mage::registry('refundrequestedcustomer_data')->getData());
      }
      return parent::_prepareForm();
  }
}
