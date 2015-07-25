<?php

class Craftsvilla_Wholesale_Block_Adminhtml_Wholesale_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('wholesale_form', array('legend'=>Mage::helper('wholesale')->__('Item information')));
     
      $fieldset->addField('sku', 'text', array(
          'label'     => Mage::helper('wholesale')->__('SKU'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'sku',
      ));
	  $fieldset->addField('productname', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Product Name'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'productname',
      ));
	  $fieldset->addField('vendorname', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Vendor Name'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'vendorname',
      ));
	  $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Name Of Customer'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'name',
      ));
	  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Email'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'email',
      ));
	  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Phone'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'phone',
      ));
	  $fieldset->addField('quantity', 'text',array(
          'label'     => Mage::helper('wholesale')->__('Qty'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'quantity',
		  'type'	  => 'number',	
      ));
	  $fieldset->addField('offer_price','text', array(
          'label'     => Mage::helper('wholesale')->__('Your Price'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'offer_price',
		  'type'	  => 'number',
      ));
	$fieldset->addField('custom', 'text', array(
		'label'     => Mage::helper('wholesale')->__('Customisation'),
		'class'     => 'required-entry',
		'required'  => false,
		'name'      => 'custom',
		));
	$fieldset->addField('expected_date','text', array(
          'label'    => Mage::helper('wholesale')->__('Expected Date').'<br/><small>('.Mage::helper('wholesale')->__('Date format:in YYYY/MM/DD').')</small>',
		  'required'  => false,
		  'name'      => 'expected_date',
          'type'    => 'datetime',
		  
		  //'renderer' = new Wholesale_Block_Adminhtml_Renderer_Date(),
      ));  
              
          
		
      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('wholesale')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));*/
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('wholesale')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('wholesale')->__('Open'),
				  
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('wholesale')->__('Qualified'),
              ),
			  array(
                  'value'     => 3,
                  'label'     => Mage::helper('wholesale')->__('Processing'),
              ),
			  array(
                  'value'     => 4,
                  'label'     => Mage::helper('wholesale')->__('Payment Received'),
              ),
			  array(
                  'value'     => 5,
                  'label'     => Mage::helper('wholesale')->__('Delivered'),
              ),
			  array(
                  'value'     => 6,
                  'label'     => Mage::helper('wholesale')->__('Closed'),
              ),
          ),
      ));
     
      $fieldset->addField('comments', 'editor', array(
          'name'      => 'comments',
          'label'     => Mage::helper('wholesale')->__('Comment'),
          'title'     => Mage::helper('wholesale')->__('Comment'),
          'style'     => 'width:500px; height:100px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
   
	  if ( Mage::getSingleton('adminhtml/session')->getWholesaleData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWholesaleData());
         
		  Mage::getSingleton('adminhtml/session')->setWholesaleData(null);
      } elseif ( Mage::registry('wholesale_data') ) {
          $form->setValues(Mage::registry('wholesale_data')->getData());
      }
      return parent::_prepareForm();
  }
}