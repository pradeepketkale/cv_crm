<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('bulkuploadcsv_form', array('legend'=>Mage::helper('bulkuploadcsv')->__('Csv information')));
     
     /* $fieldset->addField('uploaded', 'date', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Uploaded'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'uploaded',
      ));
	  */
	  $fieldset->addField('uploaded', 'date', array(
      	  'label'     => Mage::helper('bulkuploadcsv')->__('Uploaded'),
          'name'      => 'uploaded',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('FileName'),
          'required'  => true,
          'name'      => 'filename',
		  'type' => 'text',
		//  'renderer'  => 'Craftsvilla_Bulkuploadcsv_Block_Adminhtml_File_Grid_Renderer_Name',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Processing'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Completed'),
              ),
			   array(
                  'value'     => 3,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Submitted'),
              ),
			   array(
                  'value'     => 4,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Rejected'),
              ),
			  array(
                  'value'     => 5,
                  'label'     => Mage::helper('bulkuploadcsv')->__('Approved'),
              ),

          ),
      ));
	   $fieldset->addField('vendor', 'select', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Vendor'),
          'required'  => true,
          'name'      => 'vendor',
		   'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
	  ));
	   /*$fieldset->addField('productsactiveted', 'text', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Products Uploaded'),
          'required'  => true,
          'name'      => 'productsactiveted',
	  ));
	   $fieldset->addField('productsrejected', 'text', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Products Rejected'),
          'required'  => true,
          'name'      => 'productsrejected',
	  ));
	   $fieldset->addField('totalproducts', 'text', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Total Submitted'),
          'required'  => true,
          'name'      => 'totalproducts',
	  ));
	   $fieldset->addField('errorreport', 'file', array(
          'label'     => Mage::helper('bulkuploadcsv')->__('Error Report'),
          'required'  => true,
          'name'      => 'errorreport',
		  'type' => 'text',
		 //  'renderer'  => 'Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Error_Grid_Renderer_Report',
	  ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getBulkuploadcsvData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBulkuploadcsvData());
          Mage::getSingleton('adminhtml/session')->setBulkuploadcsvData(null);
      } elseif ( Mage::registry('bulkuploadcsv_data') ) {
          $form->setValues(Mage::registry('bulkuploadcsv_data')->getData());
      }
      return parent::_prepareForm();
  }
}