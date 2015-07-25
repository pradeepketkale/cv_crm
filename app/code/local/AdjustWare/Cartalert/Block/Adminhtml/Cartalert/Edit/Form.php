<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.4.1.x-1.5.0.1 - 06/07/11
 * Package:     AdjustWare_Cartalert_3.0.5_0.2.3_183688
 * Purchase ID: Y6M1PHMt9YjaYLDNXoI3HVQQ5WLuo3S19F0xW5tLYM
 * Generated:   2012-02-06 21:31:16
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Cartalert/Edit/Form.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ jZraBDeMkDkcqeqT('90480cbf1a6df13d436368743be990c4'); ?><?php

class AdjustWare_Cartalert_Block_Adminhtml_Cartalert_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
          'method' => 'post'));

      $form->setUseContainer(true);
      $this->setForm($form);
      $hlp = Mage::helper('adjcartalert');

      $fldInfo = $form->addFieldset('adjcartalert_info', array('legend'=> $hlp->__('Alert Variables')));
      
      $fldInfo->addField('store_id', 'select', array(
          'label'     => $hlp->__('Store View'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_id',
          'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
      ));
      
      $fldInfo->addField('follow_up', 'select', array(
          'label'        => $hlp->__('Follow Up'),
          'name'         => 'follow_up',
          'options'      => array(
     		'first' 	=> $hlp->__('First'),
    		'second' 	=> $hlp->__('Second'),
    		'third' 	=> $hlp->__('Third'),
          ),
      ));

      $fldInfo->addField('sheduled_at', 'date', array(
          'label'        => $hlp->__('Alert Will Be Sent On'),
          'image'        => $this->getSkinUrl('images/grid-cal.gif'),
          'format'       => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
          'name'         => 'sheduled_at',
      ));
     
      $fldInfo->addField('customer_email', 'text', array(
          'label'     => $hlp->__('Customer E-mail'),
          'class'     => 'required-entry validate-email',
          'required'  => true,
          'name'      => 'customer_email',
      ));
      $fldInfo->addField('customer_fname', 'text', array(
          'label'     => $hlp->__('Customer First Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'customer_fname',
      ));
      $fldInfo->addField('customer_lname', 'text', array(
          'label'     => $hlp->__('Customer Last Name'),
          'name'      => 'customer_lname',
      ));
      
      $fldInfo->addField('products', 'textarea', array(
          'label'     => $hlp->__('Produsts'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'products',
          'style'     => 'width:35em;height:15em;',
      ));
      $fldInfo->addField('is_preprocessed', 'hidden', array(
          'name'      => 'is_preprocessed',
          'value'     => 1,
      ));

      if ( Mage::registry('cartalert_data') ) {
          $form->setValues(Mage::registry('cartalert_data')->getData());
      }
      
      return parent::_prepareForm();
  }
} } 