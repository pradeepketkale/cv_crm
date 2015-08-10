<?php

class Craftsvilla_Disputecusremarks_Block_Adminhtml_Disputecusremarks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('disputecusremarks_form', array('legend'=>Mage::helper('disputecusremarks')->__('Item information')));
     
      $fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('disputecusremarks')->__('shipment Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));
	$fieldset->addField('vendor_id', 'text', array(
          'label'     => Mage::helper('disputecusremarks')->__('vendor Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendor_id',
      ));
	$fieldset->addField('vendor_name', 'text', array(
          'label'     => Mage::helper('disputecusremarks')->__('vendor Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendor_name',
      ));
$fieldset->addField('remarks', 'text', array(
          'label'     => Mage::helper('disputecusremarks')->__('Remarks'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'remarks',
      ));

      
     
      if ( Mage::getSingleton('adminhtml/session')->getDisputecusremarksData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDisputecusremarksData());
          Mage::getSingleton('adminhtml/session')->setDisputecusremarksData(null);
      } elseif ( Mage::registry('disputecusremarks_data') ) {
          $form->setValues(Mage::registry('disputecusremarks_data')->getData());
      }
      return parent::_prepareForm();
  }
}
