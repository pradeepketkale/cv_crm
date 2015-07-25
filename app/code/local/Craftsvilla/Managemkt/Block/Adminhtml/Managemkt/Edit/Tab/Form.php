<?php

class Craftsvilla_Managemkt_Block_Adminhtml_Managemkt_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('managemkt_form', array('legend'=>Mage::helper('managemkt')->__('Manage Marketing')));
     
      $fieldset->addField('activity', 'select', array(
          'label'     => Mage::helper('managemkt')->__('Activity'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'activity',
		  
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('managemkt')->__('Facebook Post'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('managemkt')->__('Emailer'),
              ),
			  array(
                  'value'     => 3,
                  'label'     => Mage::helper('managemkt')->__('Homepage Banner'),
              ),
			  array(
                  'value'     => 4,
                  'label'     => Mage::helper('managemkt')->__('Homepage Products'),
              ),
			  array(
                  'value'     => 5,
                  'label'     => Mage::helper('managemkt')->__('Featured Seller'),
              ),
			  array(
                  'value'     => 6,
                  'label'     => Mage::helper('managemkt')->__('Guaranteed Sale'),
              ),
          ),
      ));

      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('managemkt')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));*/
		 $fieldset->addField('vendorname', 'select', array(
          'label'     => Mage::helper('managemkt')->__('Vendor Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendorname',
		  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('managemkt')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('managemkt')->__('Requested'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('managemkt')->__('Accepted'),
              ),
			  array(
                  'value'     => 3,
                  'label'     => Mage::helper('managemkt')->__('Executed'),
              ),array(
                  'value'     => 4,
                  'label'     => Mage::helper('managemkt')->__('On Hold'),
              ),array(
                  'value'     => 5,
                  'label'     => Mage::helper('managemkt')->__('Declined'),
              ),array(
                  'value'     => 6,
                  'label'     => Mage::helper('managemkt')->__('Cancelled'),
              ),
          ),
      ));
	  
	  $fieldset->addField('start_date', 'date', array(
      	  'label'     => Mage::helper('managemkt')->__('Start Date'),
          'name'      => 'start_date',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  $fieldset->addField('end_date', 'date', array(
      	  'label'     => Mage::helper('managemkt')->__('End Date'),
          'name'      => 'end_date',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  $fieldset->addField('comment_url', 'textarea', array(
          'label'     => Mage::helper('managemkt')->__('Comment/Product_Url'),
		  'name'      => 'comment_url',
      ));
	  
/*      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('managemkt')->__('Content'),
          'title'     => Mage::helper('managemkt')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getmanagemktData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getmanagemktData());
          Mage::getSingleton('adminhtml/session')->setmanagemktData(null);
      } elseif ( Mage::registry('managemkt_data') ) {
          $form->setValues(Mage::registry('managemkt_data')->getData());
      }
      return parent::_prepareForm();
  }
}