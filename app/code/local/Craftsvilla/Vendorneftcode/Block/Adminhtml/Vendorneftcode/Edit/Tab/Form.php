<?php

class Craftsvilla_Vendorneftcode_Block_Adminhtml_Vendorneftcode_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('vendorneftcode_form', array('legend'=>Mage::helper('vendorneftcode')->__('NEFT information')));
     
      $fieldset->addField('vendor_id', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Vendor ID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendor_id',
      ));

      $fieldset->addField('vendor_name', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Vendor Name'),
          'name'      => 'vendor_name',
	  ));
	  $fieldset->addField('merchant_id_city', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('NEFT Code'),
          'name'      => 'merchant_id_city',
	  ));
          $fieldset->addField('catalog_privileges', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Catalog Privileges'),
          'name'      => 'catalog_privileges',
	  ));
          $fieldset->addField('logistics_privileges', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Logistics Privileges'),
          'name'      => 'logistics_privileges',
	  ));
          $fieldset->addField('payment_privileges', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Payment Privileges'),
          'name'      => 'payment_privileges',
	  ));
          $fieldset->addField('bulk_privileges', 'text', array(
          'label'     => Mage::helper('vendorneftcode')->__('Bulk Privileges'),
          'name'      => 'bulk_privileges',
    ));
		
      /*$fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('vendorneftcode')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('vendorneftcode')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('vendorneftcode')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('vendorneftcode')->__('Content'),
          'title'     => Mage::helper('vendorneftcode')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getVendorneftcodeData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getVendorneftcodeData());
          Mage::getSingleton('adminhtml/session')->setVendorneftcodeData(null);
      } elseif ( Mage::registry('vendorneftcode_data') ) {
          $form->setValues(Mage::registry('vendorneftcode_data')->getData());
      }
      return parent::_prepareForm();
  }
}
