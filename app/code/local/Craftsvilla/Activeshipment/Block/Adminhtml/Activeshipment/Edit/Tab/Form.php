<?php

class Craftsvilla_Activeshipment_Block_Adminhtml_Activeshipment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('activeshipment_form', array('legend'=>Mage::helper('activeshipment')->__('Active Shipment Details')));
     
      $fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('activeshipment')->__('Shipment Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));

      $fieldset->addField('cust_status', 'text', array(
          'label'     => Mage::helper('activeshipment')->__('Cust Status'),
          //'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'cust_status',
      ));
	  
	  $fieldset->addField('primary_category', 'text', array(
          'label'     => Mage::helper('activeshipment')->__('primary_category'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'primary_category',
      ));
	   $fieldset->addField('expected_shipingdate', 'date', array(
      	  'label'     => Mage::helper('activeshipment')->__('Expected Shiping Date'),
          'name'      => 'expected_shipingdate',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  $fieldset->addField('vendor_claimedfrom', 'select', array(
          'label'     => Mage::helper('activeshipment')->__('Vendor Claimed From'),
          'class'     => 'required-entry',
          'required'  => true,
		  'type' => 'options',
          'name'      => 'vendor_claimedfrom',
		  'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(true),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	  
	  $fieldset->addField('vendor_claimedto', 'select', array(
          'label'     => Mage::helper('activeshipment')->__('Vendor Claimed To'),
          'class'     => 'required-entry',
          'required'  => true,
		  'type' => 'options',
          'name'      => 'vendor_claimedto',
		  'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(true),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	 $fieldset->addField('claimed_date', 'date', array(
      	  'label'     => Mage::helper('activeshipment')->__('Claimed Date'),
          'name'      => 'claimed_date',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  	
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('activeshipment')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('activeshipment')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('activeshipment')->__('Disabled'),
              ),
          ),
      ));
     
     
     
      if ( Mage::getSingleton('adminhtml/session')->getActiveshipmentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getActiveshipmentData());
          Mage::getSingleton('adminhtml/session')->setActiveshipmentData(null);
      } elseif ( Mage::registry('activeshipment_data') ) {
          $form->setValues(Mage::registry('activeshipment_data')->getData());
      }
      return parent::_prepareForm();
  }
}