<?php

class Craftsvilla_Agentpayout_Block_Adminhtml_Agentpayout_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('agentpayout_form', array('legend'=>Mage::helper('agentpayout')->__('Payout Information')));
     
      $fieldset->addField('shipment_id', 'label', array(
          'label'     => Mage::helper('agentpayout')->__('Shipment Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'shipment_id',
      ));

      $fieldset->addField('agentpayout_status', 'select', array(
          'label'     => Mage::helper('agentpayout')->__('Payout Status'),
          'name'      => 'agentpayout_status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('agentpayout')->__('Paid'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('agentpayout')->__('Unpaid'),
              ),
			  array(
                  'value'     => 2,
                  'label'     => Mage::helper('agentpayout')->__('Refunded'),
              ),
          ),
      ));
     
      $fieldset->addField('agentpayout_update_time', 'date', array(
      	  'label'     => Mage::helper('agentpayout')->__('Payout Date'),
          'name'      => 'agentpayout_update_time',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
      
	  
	  $fieldset->addField('comment', 'text', array(
      	  'label'     => Mage::helper('agentpayout')->__('Comment'),
          'name'      => 'comment',
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getAgentpayoutData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getAgentpayoutData());
          Mage::getSingleton('adminhtml/session')->setAgentpayoutData(null);
      } elseif ( Mage::registry('agentpayout_data') ) {
          $form->setValues(Mage::registry('agentpayout_data')->getData());
      }
      return parent::_prepareForm();
  }
}