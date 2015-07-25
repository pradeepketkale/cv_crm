<?php

class Craftsvilla_Homepage_Block_Adminhtml_Homepage_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('homepage_form', array('legend'=>Mage::helper('homepage')->__('Product information')));
     
    /*  $fieldset->addField('image', 'text', array(
          'label'     => Mage::helper('homepage')->__('Image'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'image',
      ));
	  */
	  $fieldset->addField('image', 'image', array(
          'label'     => Mage::helper('homepage')->__('Image'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'image',
      ));

	 $fieldset->addField('sku', 'text', array(
		  'label'     => Mage::helper('homepage')->__('SKU'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'sku',
		  ));

	 $fieldset->addField('name', 'text', array(
		  'label'     => Mage::helper('homepage')->__('Name'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'name',
			  ));
		
	 $fieldset->addField('description', 'text', array(
		  'label'     => Mage::helper('homepage')->__('Description'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'description',
			  ));
		
	 $fieldset->addField('price', 'text', array(
		  'label'     => Mage::helper('homepage')->__('Price'),
		  'class'     => 'required-entry',
		  'required'  => true,
		  'name'      => 'price',
			  ));


		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('homepage')->__('Status'),
          'name'      => 'status',
		  'value'     => '2',
		  'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('homepage')->__('Assigned'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('homepage')->__('Not Assigned'),
				
              ),
			  
          ),
		  
      ));
     
     /* $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('homepage')->__('Content'),
          'title'     => Mage::helper('homepage')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getHomepageData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getHomepageData());
          Mage::getSingleton('adminhtml/session')->setHomepageData(null);
      } elseif ( Mage::registry('homepage_data') ) {
          $form->setValues(Mage::registry('homepage_data')->getData());
      }
      return parent::_prepareForm();
  }
}