<?php

class Craftsvilla_Shipmentpayout_Block_Adminhtml_Shipmentpayout_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shipmentpayout_form', array('legend'=>Mage::helper('shipmentpayout')->__('Shipmentpayout information')));
     
      $fieldset->addField('shipment_id', 'label', array(
          'label'     => Mage::helper('shipmentpayout')->__('Shipment Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));

      $fieldset->addField('order_id', 'label', array(
          'label'     => Mage::helper('shipmentpayout')->__('order_id'),
          'required'  => true,
          'name'      => 'order_id',
	  ));
// add a extra field name `Refunded` on below by Dileswar on dated- 19-02-2013 		
      $fieldset->addField('shipmentpayout_status', 'select', array(
          'label'     => Mage::helper('shipmentpayout')->__('Payout Status'),
          'name'      => 'shipmentpayout_status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shipmentpayout')->__('Paid'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('shipmentpayout')->__('Unpaid'),
              ),
			  array(
                  'value'     => 2,
                  'label'     => Mage::helper('shipmentpayout')->__('Refunded'),
              ),
          ),
      ));
     
      $fieldset->addField('shipmentpayout_update_time', 'date', array(
      	  'label'     => Mage::helper('shipmentpayout')->__('Payout Date'),
          'name'      => 'shipmentpayout_update_time',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
      
      $fieldset->addField('citibank_utr', 'text', array(
      	  'label'     => Mage::helper('shipmentpayout')->__('Citi Bank UTR No.'),
          'name'      => 'citibank_utr',
      ));
	  
	  $fieldset->addField('intshipingcost', 'text', array(
      	  'label'     => Mage::helper('shipmentpayout')->__('InterShippingCost.'),
          'name'      => 'intshipingcost',
      ));
	  
	  $fieldset->addField('adjustment','text',array(
	  		'label'		=> Mage::helper('shipmentpayout')->__('adjustment'),
	  		'name'		=> 'adjustment'
	  ));
	  $fieldset->addField('comment', 'text', array(
      	  'label'     => Mage::helper('shipmentpayout')->__('Comment'),
          'name'      => 'comment',
      ));
	  
     
      if ( Mage::getSingleton('adminhtml/session')->getShipmentpayoutData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShipmentpayoutData());
          Mage::getSingleton('adminhtml/session')->setShipmentpayoutData(null);
      } elseif ( Mage::registry('shipmentpayout_data') ) {
          $form->setValues(Mage::registry('shipmentpayout_data')->getData());
      }

      return parent::_prepareForm();
  }
}
