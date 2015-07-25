<?php

class Craftsvilla_Couponrequest_Block_Adminhtml_Couponrequest_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('couponrequest_form', array('legend'=>Mage::helper('couponrequest')->__('Coupon information')));
     
      $fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('couponrequest')->__('Shipment_id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));
       $fieldset->addField('price', 'text', array(
          'label'     => Mage::helper('couponrequest')->__('Price'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'price',
      ));
     
      
      /*$fieldset->addField('expire_date_ofcoupon', 'date', array(
      	  'label'     => Mage::helper('couponrequest')->__('Expire_Date_OfCoupon'),
          'format' 	  => 'yyyy-MM-dd',
          'image'     => $this->getSkinUrl('images/grid-cal.gif'),
          'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      	  'name'      => 'expire_date_ofcoupon',
      ));*/
     
          
     
      if ( Mage::getSingleton('adminhtml/session')->getCouponrequestData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCouponrequestData());
          Mage::getSingleton('adminhtml/session')->setCouponrequestData(null);
      } elseif ( Mage::registry('couponrequest_data') ) {
          $form->setValues(Mage::registry('couponrequest_data')->getData());
      }
      return parent::_prepareForm();
  }
}
