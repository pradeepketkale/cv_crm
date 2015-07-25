<?php

class Craftsvilla_Bulkinventoryupdate_Block_Adminhtml_Bulkinventoryupdate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('bulkinventoryupdate_form', array('legend'=>Mage::helper('bulkinventoryupdate')->__('Csv information')));
     
      $fieldset->addField('uploaded', 'text', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('Uploaded'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'uploaded',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('FileName'),
          'required'  => true,
          'name'      => 'filename',
		  'type' => 'text',
	
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('bulkinventoryupdate')->__('Processing'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('bulkinventoryupdate')->__('Completed'),
              ),
			   array(
                  'value'     => 3,
                  'label'     => Mage::helper('bulkinventoryupdate')->__('Submitted'),
              ),
			   array(
                  'value'     => 4,
                  'label'     => Mage::helper('bulkinventoryupdate')->__('Rejected'),
              ),
			   array(
                  'value'     => 5,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Approved'),
              ),
          ),
      ));
	   $fieldset->addField('vendor', 'text', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('Vendor'),
          'required'  => true,
          'name'      => 'vendor',
	  ));
	  
	   $fieldset->addField('totalproducts', 'text', array(
          'label'     => Mage::helper('bulkinventoryupdate')->__('Total Products'),
          'required'  => true,
          'name'      => 'totalproducts',
	  ));
	  if ( Mage::getSingleton('adminhtml/session')->getBulkinventoryupdateData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBulkinventoryupdateData());
          Mage::getSingleton('adminhtml/session')->setBulkinventoryupdateData(null);
      } elseif ( Mage::registry('bulkinventoryupdate_data') ) {
          $form->setValues(Mage::registry('bulkinventoryupdate_data')->getData());
      }
      return parent::_prepareForm();
  }
}