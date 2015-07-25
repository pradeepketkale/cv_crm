<?php

class Craftsvilla_Productdownloadreq_Block_Adminhtml_Productdownloadreq_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('productdownloadreq_form', array('legend'=>Mage::helper('productdownloadreq')->__('Manage Request')));
     
      $fieldset->addField('activity', 'select', array(
          'label'     => Mage::helper('productdownloadreq')->__('Activity'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'activity',
		  
          'values'    => array(
		  
		    array(
                  'value'     => 1,
                  'label'     => Mage::helper('productdownloadreq')->__('Full Product Download'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('productdownloadreq')->__('Inventory Download'),
              ),
			 
          ),
      ));

      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('managemkt')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));*/
		 $fieldset->addField('vendorname', 'select', array(
          'label'     => Mage::helper('productdownloadreq')->__('Vendor Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendorname',
		  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('productdownloadreq')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('productdownloadreq')->__('Requested'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('productdownloadreq')->__('Completed'),
              ),
			            ),
      ));
	  
	 
      if ( Mage::getSingleton('adminhtml/session')->getproductdownloadreqData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getproductdownloadreqData());
          Mage::getSingleton('adminhtml/session')->setproductdownloadreqData(null);
      } elseif ( Mage::registry('productdownloadreq_data') ) {
          $form->setValues(Mage::registry('productdownloadreq_data')->getData());
      }
      return parent::_prepareForm();
  }
}