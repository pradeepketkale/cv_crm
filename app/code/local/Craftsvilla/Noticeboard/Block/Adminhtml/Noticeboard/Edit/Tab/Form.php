<?php

class Craftsvilla_Noticeboard_Block_Adminhtml_Noticeboard_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('noticeboard_form', array('legend'=>Mage::helper('noticeboard')->__('Notice information')));
     
     /* $fieldset->addField('noticeid', 'text', array(
          'label'     => Mage::helper('noticeboard')->__('noticeId'),
          'required'  => true,
          'name'      => 'noticeid',
		));*/
	  $fieldset->addField('created', 'date', array(
      	  'label'     => Mage::helper('noticeboard')->__('Created'),
          'name'      => 'created',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));

      $fieldset->addField('content', 'text', array(
          'label'     => Mage::helper('noticeboard')->__('Content'),
          'required'  => true,
          'name'      => 'content',
		));
	  
	  
	  $fieldset->addField('image', 'file', array(
          'label'     => Mage::helper('noticeboard')->__('Image'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'image',
      ));

		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('noticeboard')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('noticeboard')->__('Approved'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('noticeboard')->__('Not Approved'),
              ),
			
          ),
      ));
	  
	   $fieldset->addField('type', 'select', array(
          'label'     => Mage::helper('noticeboard')->__('Type'),
          'name'      => 'type',
	      'value' => 3,
          'values'    => array(
              array(
                  'value'     => 3,
                  'label'     => Mage::helper('noticeboard')->__('Admin'),
              ),

              array(
                  'value'     => 4,
                  'label'     => Mage::helper('noticeboard')->__('Seller'),
              ),
			
          ),
      ));
	 
     
      if ( Mage::getSingleton('adminhtml/session')->getNoticeboardData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getNoticeboardData());
          Mage::getSingleton('adminhtml/session')->setNoticeboardData(null);
      } elseif ( Mage::registry('noticeboard_data') ) {
          $form->setValues(Mage::registry('noticeboard_data')->getData());
      }
      return parent::_prepareForm();
  }
}