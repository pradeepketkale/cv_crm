<?php

class Craftsvilla_Utrreport_Block_Adminhtml_Utrreport_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('utrreport_form', array('legend'=>Mage::helper('utrreport')->__('UTR Details')));
     
      $fieldset->addField('utrno', 'text', array(
          'label'     => Mage::helper('utrreport')->__('UTR Number'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'utrno',
      ));
	$fieldset->addField('payin_date', 'date', array(
          'label'     => Mage::helper('utrreport')->__('Pay In Date'),
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
          'name'      => 'payin_date',
      ));
	  $fieldset->addField('amount','text',array(
	  		'label'		=> Mage::helper('utrreport')->__('Amount'),
	  		'name'		=> 'amount'
	  ));	
      $fieldset->addField('balance','text',array(
	  		'label'		=> Mage::helper('utrreport')->__('Balance Amount'),
	  		'name'		=> 'balance'
	  ));
	  $fieldset->addField('utrpaid','text',array(
	  		'label'		=> Mage::helper('utrreport')->__('UTR Paid'),
	  		'name'		=> 'utrpaid'
	  ));
		
     /* $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('utrreport')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('utrreport')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('utrreport')->__('Disabled'),
              ),
          ),
      ));*/
     
      /*$fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('utrreport')->__('Content'),
          'title'     => Mage::helper('utrreport')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getUtrreportData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getUtrreportebData());
          Mage::getSingleton('adminhtml/session')->setUtrreportData(null);
      } elseif ( Mage::registry('utrreport_data') ) {
          $form->setValues(Mage::registry('utrreport_data')->getData());
      }
      return parent::_prepareForm();
  }
}