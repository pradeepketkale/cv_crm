<?php

class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentgrid_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('codrefundshipmentgrid_form', array('legend'=>Mage::helper('codrefundshipmentgrid')->__('codrefundshipmentgrid Edit')));
     
      /*$fieldset->addField('order_id', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Order Id'),
          'class'     => 'required-entry validate-number',
          'required'  => true,
          'name'      => 'order_id',
      ));*/
	$fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Shipment Id'),
          'class'     => 'required-entry validate-number',
          'required'  => true,
          'name'      => 'shipment_id',
      ));
	$fieldset->addField('cust_name', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Cust Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'cust_name',
      ));
	$fieldset->addField('accountno', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Account No'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'accountno',
      ));	
	$fieldset->addField('ifsccode', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('IFSC Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ifsccode',
      ));
	$fieldset->addField('paymentamount', 'text', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Paymentamount'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'paymentamount',
      ));

	/*$fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('codrefundshipmentgrid')->__('Payout Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('codrefundshipmentgrid')->__('Paid'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('codrefundshipmentgrid')->__('Unpaid'),
              ),
			  array(
                  'value'     => 2,
                  'label'     => Mage::helper('codrefundshipmentgrid')->__('Refunded'),
              ),
          ),
      ));*/
     
     
      if ( Mage::getSingleton('adminhtml/session')->getCodrefundshipmentgridData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCodrefundshipmentgridData());
          Mage::getSingleton('adminhtml/session')->setCodrefundshipmentgridData(null);
      } elseif ( Mage::registry('codrefundshipmentgrid_data') ) {
          $form->setValues(Mage::registry('codrefundshipmentgrid_data')->getData());
      }
      return parent::_prepareForm();
  }
}
