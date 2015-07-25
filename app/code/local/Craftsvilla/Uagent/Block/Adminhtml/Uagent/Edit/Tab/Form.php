<?php

class Craftsvilla_Uagent_Block_Adminhtml_Uagent_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('uagent_form', array('legend'=>Mage::helper('uagent')->__('Agent information')));
     
      $fieldset->addField('agent_name', 'text', array(
          'label'     => Mage::helper('uagent')->__('Agent Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'agent_name',
      ));
	  $fieldset->addField('created_time', 'date', array(
      	  'label'     => Mage::helper('uagent')->__('Created Date'),
          'name'      => 'created_time',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  
	  $fieldset->addField('agent_attn', 'text', array(
          'label'     => Mage::helper('uagent')->__('Agent Attn'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'agent_attn',
      ));
	  
	  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('uagent')->__('Agent Email'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'email',
      ));
	  
	  $fieldset->addField('street', 'text', array(
          'label'     => Mage::helper('uagent')->__('Street'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'street',
      ));
	  
	  $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('uagent')->__('City'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'city',
      ));
	  
	  $fieldset->addField('zip', 'text', array(
          'label'     => Mage::helper('uagent')->__('Zip Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'zip',
      ));
	  
	  $fieldset->addField('region', 'text', array(
          'label'     => Mage::helper('uagent')->__('State'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'region',
      ));
	  
	  $fieldset->addField('country_id', 'select', array(
          'label'     => Mage::helper('uagent')->__('Country Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'country_id',
		  'values'    => Mage::getModel('adminhtml/system_config_source_country') ->toOptionArray(),
          //'onchange' => 'getstate(this)',
      ));
	 
	  $fieldset->addField('telephone', 'text', array(
          'label'     => Mage::helper('uagent')->__('Cont No.'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'telephone',
      ));
	  $fieldset->addField('bank_account_number', 'text', array(
          'label'     => Mage::helper('uagent')->__('Bank Account Number'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'bank_account_number',
      ));
	  $fieldset->addField('bank_name', 'text', array(
          'label'     => Mage::helper('uagent')->__('Bank Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'bank_name',
      ));
	  $fieldset->addField('check_pay_to', 'text', array(
          'label'     => Mage::helper('uagent')->__('Check To Pay'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'check_pay_to',
      ));
	  $fieldset->addField('bank_ifsc_code', 'text', array(
          'label'     => Mage::helper('uagent')->__('Ifsc Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'bank_ifsc_code',
      ));
	  $fieldset->addField('password', 'text', array(
          'label'     => Mage::helper('uagent')->__('Password'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'password',
      ));
	  $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('uagent')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('uagent')->__('Active'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('uagent')->__('Deactive'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getUagentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getUagentData());
          Mage::getSingleton('adminhtml/session')->setUagentData(null);
      } elseif ( Mage::registry('uagent_data') ) {
          $form->setValues(Mage::registry('uagent_data')->getData());
      }
      return parent::_prepareForm();
  }
}