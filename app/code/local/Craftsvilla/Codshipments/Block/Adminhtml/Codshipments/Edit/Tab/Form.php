<?php

class Craftsvilla_Codshipments_Block_Adminhtml_Codshipments_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
 /* protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('codshipments_form', array('legend'=>Mage::helper('codshipments')->__('Item information')));
     
      $fieldset->addField('waybill', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Waybill'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'waybill',
      ));

      $fieldset->addField('orderno', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Order No'),
          'required'  => false,
          'name'      => 'orderno',
	  ));
	  
	  $fieldset->addField('consigneename', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Consignee Name'),
          'required'  => false,
          'name'      => 'consigneename',
	  ));
	  $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('codshipments')->__('City'),
          'required'  => false,
          'name'      => 'city',
	  ));
	  $fieldset->addField('state', 'text', array(
          'label'     => Mage::helper('codshipments')->__('State'),
          'required'  => false,
          'name'      => 'state',
	  ));
	  $fieldset->addField('country', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Country'),
          'required'  => false,
          'name'      => 'country',
	  ));
	  $fieldset->addField('address', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Address'),
          'required'  => false,
          'name'      => 'address',
	  ));
	  $fieldset->addField('pincode', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Pincode'),
          'required'  => false,
          'name'      => 'pincode',
	  ));
	  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Phone'),
          'required'  => false,
          'name'      => 'phone',
	  ));
	  $fieldset->addField('mobile', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Mobile'),
          'required'  => false,
          'name'      => 'mobile',
	  ));
	  $fieldset->addField('paymentmode', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Payment Mode'),
          'required'  => false,
          'name'      => 'paymentmode',
	  ));
	  $fieldset->addField('packageamount', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Package Amount'),
          'required'  => false,
          'name'      => 'packageamount',
	  ));
	  $fieldset->addField('codamount', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Cod Amount'),
          'required'  => false,
          'name'      => 'codamount',
	  ));
	  $fieldset->addField('productshipped', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Product To Be Shipped'),
          'required'  => true,
          'name'      => 'productshipped',
		   'name'      => 'handicraft',
          'value'     => 'Handicraft Item', 
	  ));
	  $fieldset->addField('shippingclient', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Shipping Client'),
          'required'  => false,
          'name'      => 'shippingclient',
	  ));
	  $fieldset->addField('shipclientaddress', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Shipping Client Address'),
          'required'  => false,
          'name'      => 'shipclientaddress',
	  ));
	  $fieldset->addField('shipclientphone', 'text', array(
          'label'     => Mage::helper('codshipments')->__('Shipping Client Phone'),
          'required'  => false,
          'name'      => 'shipclientphone',
	  ));
	
		*/
     /* $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('codshipments')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('codshipments')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('codshipments')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('codshipments')->__('Content'),
          'title'     => Mage::helper('codshipments')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
     /* if ( Mage::getSingleton('adminhtml/session')->getCodshipmentsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCodshipmentsData());
          Mage::getSingleton('adminhtml/session')->setCodshipmentsData(null);
      } elseif ( Mage::registry('codshipments_data') ) {
          $form->setValues(Mage::registry('codshipments_data')->getData());
      }
      return parent::_prepareForm();
  }*/
}