<?php

class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('disputeraised_form', array('legend'=>Mage::helper('disputeraised')->__('Item information')));
     
      /*$fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('disputeraised')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      */

      $fieldset->addField('content', 'text', array(
          'label'     => Mage::helper('disputeraised')->__('Content'),
          'required'  => false,
          'name'      => 'content',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('disputeraised')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('disputeraised')->__('Open'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('disputeraised')->__('Closed'),
              ),
          ),
      ));
     
     /* $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('disputeraised')->__('Content'),
          'title'     => Mage::helper('disputeraised')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     */
      if ( Mage::getSingleton('adminhtml/session')->getDisputeraisedData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getDisputeraisedData());
          Mage::getSingleton('adminhtml/session')->setDisputeraisedData(null);
      } elseif ( Mage::registry('disputeraised_data') ) {
          $form->setValues(Mage::registry('disputeraised_data')->getData());
      }
      return parent::_prepareForm();
  }
}