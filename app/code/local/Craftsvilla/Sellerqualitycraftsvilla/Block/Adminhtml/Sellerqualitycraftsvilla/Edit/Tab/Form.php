<?php

class Craftsvilla_Sellerqualitycraftsvilla_Block_Adminhtml_Sellerqualitycraftsvilla_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('sellerqualitycraftsvilla_form', array('legend'=>Mage::helper('sellerqualitycraftsvilla')->__('Seller Quality Information')));
     
      $fieldset->addField('vendor_id', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('vendor ID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendor_id',
      ));

      $fieldset->addField('vendor_name', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('vendor Name'),
          'required'  => true,
          'name'      => 'vendor_name',
	  ));
	  /*$fieldset->addField('total_shipments_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('shipments90DAY'),
          'required'  => true,
          'name'      => 'total_shipments_90_days',
	  ));
	  $fieldset->addField('total_shipments_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('shipments30DAY'),
          'required'  => true,
          'name'      => 'total_shipments_30_days',
	  ));
	  $fieldset->addField('refund_ratio_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('refund%90DAY'),
          'required'  => true,
          'name'      => 'refund_ratio_90_days',
	  ));
	  $fieldset->addField('refund_ratio_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('refund%30DAY'),
          'required'  => true,
          'name'      => 'refund_ratio_30_days',
	  ));
	  $fieldset->addField('dispute_ratio_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('dispute%90DAY'),
          'required'  => true,
          'name'      => 'dispute_ratio_90_days',
	  ));
	  $fieldset->addField('dispute_ratio_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('dispute%30DAY'),
          'required'  => true,
          'name'      => 'dispute_ratio_30_days',
	  ));
	  $fieldset->addField('dispatch_prepaid_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('dispatchprepaid90'),
          'required'  => true,
          'name'      => 'dispatch_prepaid_90_days',
	  ));
	  $fieldset->addField('dispatch_prepaid_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('dispatchprepaid30'),
          'required'  => true,
          'name'      => 'dispatch_prepaid_30_days',
	  ));
	  $fieldset->addField('cod_return_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('codreturn90'),
          'required'  => true,
          'name'      => 'cod_return_90_days',
	  ));
	  $fieldset->addField('cod_return_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('codreturn30'),
          'required'  => true,
          'name'      => 'cod_return_30_days',
	  ));
	  $fieldset->addField('cod_cancel_90_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('codcancel90'),
          'required'  => true,
          'name'      => 'cod_cancel_90_days',
	  ));
	  $fieldset->addField('cod_cancel_30_days', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('codcancel30'),
          'required'  => true,
          'name'      => 'cod_cancel_30_days',
	  ));
	  $fieldset->addField('cod_ratio', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('codratio'),
          'required'  => true,
          'name'      => 'cod_ratio',
	  ));
	  $fieldset->addField('craftsvilla_seller_rating', 'text', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('craftsvillasellerrating'),
          'required'  => true,
          'name'      => 'craftsvilla_seller_rating',
	  ));
	  */
	  
	  
	  
	  
		
     /* $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('sellerqualitycraftsvilla')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('sellerqualitycraftsvilla')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('sellerqualitycraftsvilla')->__('Content'),
          'title'     => Mage::helper('sellerqualitycraftsvilla')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getSellerqualitycraftsvillaData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSellerqualitycraftsvillaData());
          Mage::getSingleton('adminhtml/session')->setSellerqualitycraftsvillaData(null);
      } elseif ( Mage::registry('sellerqualitycraftsvilla_data') ) {
          $form->setValues(Mage::registry('sellerqualitycraftsvilla_data')->getData());
      }
      return parent::_prepareForm();
  }
}
