<?php

class Craftsvilla_Ebslink_Block_Adminhtml_Ebslink_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('ebslink_form', array('legend'=>Mage::helper('ebslink')->__('Ebslink Details')));
     
      $fieldset->addField('order_no', 'text', array(
          'label'     => Mage::helper('ebslink')->__('Order No'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'order_no',
      ));

      
      $fieldset->addField('ebslinkurl', 'text', array(
          'name'      => 'ebslinkurl',
          'label'     => Mage::helper('ebslink')->__('Ebslinkurl'),
          'title'     => Mage::helper('ebslink')->__('EbsLinkurl'),
          'wysiwyg'   => false,
          'required'  => true,
      ));
	  
	   $fieldset->addField('comment', 'select', array(
          'label'     => Mage::helper('ebslink')->__('Comments'),
		  'name'      => 'comment',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('ebslink')->__('COD Payment Needed'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('ebslink')->__('Interested Will Pay'),
              ),
			  array(
                  'value'     => 3,
                  'label'     => Mage::helper('ebslink')->__('Not Interested Cancel'),
              ),
			  array(
                  'value'     => 4,
                  'label'     => Mage::helper('ebslink')->__('Busy Call Again'),
              ),
			  array(
                  'value'     => 5,
                  'label'     => Mage::helper('ebslink')->__('Not Reachable'),
              ),
			  array(
                  'value'     => 6,
                  'label'     => Mage::helper('ebslink')->__('International Call'),
              ),
			  array(
                  'value'     => 7,
                  'label'     => Mage::helper('ebslink')->__('Other'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getEbslinkData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getEbslinkData());
          Mage::getSingleton('adminhtml/session')->setEbslinkData(null);
      } elseif ( Mage::registry('ebslink_data') ) {
          $form->setValues(Mage::registry('ebslink_data')->getData());
      }
      return parent::_prepareForm();
  }
}