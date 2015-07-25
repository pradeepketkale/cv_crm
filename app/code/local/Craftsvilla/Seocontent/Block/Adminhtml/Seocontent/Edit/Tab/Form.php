<?php

class Craftsvilla_Seocontent_Block_Adminhtml_Seocontent_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('seocontent_form', array('legend'=>Mage::helper('seocontent')->__('Item information')));
     
      $fieldset->addField('category', 'select', array(
          'label'     => Mage::helper('seocontent')->__('Category'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'category',
		   'values'    => array(
              array(
                  'value'     => 74,
                  'label'     => Mage::helper('seocontent')->__('Sarees'),
              ),

              array(
                  'value'     => 6,
                  'label'     => Mage::helper('seocontent')->__('Jewellery'),
              ),
			  array(
                  'value'     => 33,
                  'label'     => Mage::helper('seocontent')->__('Necklaces'),
              ),
			array(
                  'value'     => 54,
                  'label'     => Mage::helper('seocontent')->__('Anklets'),
              ),
			   array(
                  'value'     => 55,
                  'label'     => Mage::helper('seocontent')->__('Bracelets n Bangles'),
              ),
			   array(
                  'value'     => 214,
                  'label'     => Mage::helper('seocontent')->__('Rings'),
              ),
			  array(
                  'value'     => 248,
                  'label'     => Mage::helper('seocontent')->__('Pendants'),
              ),
			   array(
                  'value'     => 34,
                  'label'     => Mage::helper('seocontent')->__('Earrings'),
              ),
			   array(
                  'value'     => 358,
                  'label'     => Mage::helper('seocontent')->__('Banarasi Sarees'),
              ),
			   array(
                  'value'     => 359,
                  'label'     => Mage::helper('seocontent')->__('Bandhani Sarees'),
              ),
			   array(
                  'value'     => 360,
                  'label'     => Mage::helper('seocontent')->__('Chiffon Sarees'),
              ),
			   array(
                  'value'     => 361,
                  'label'     => Mage::helper('seocontent')->__('Cotton Sarees'),
              ),
			   array(
                  'value'     => 362,
                  'label'     => Mage::helper('seocontent')->__('Cotton Silk Sarees'),
              ),
			   array(
                  'value'     => 363,
                  'label'     => Mage::helper('seocontent')->__('Designer Sarees'),
              ),
			   array(
                  'value'     => 365,
                  'label'     => Mage::helper('seocontent')->__('Georgette Sarees'),
              ),
			  array(
                  'value'     => 366,
                  'label'     => Mage::helper('seocontent')->__('Handwoven Sarees'),
              ),
			  array(
                  'value'     => 367,
                  'label'     => Mage::helper('seocontent')->__('Heavy Work Sarees'),
              ),
			  array(
                  'value'     => 368,
                  'label'     => Mage::helper('seocontent')->__('Jacquard Sarees'),
              ),
			  array(
                  'value'     => 369,
                  'label'     => Mage::helper('seocontent')->__('Kanchivaram Sarees'),
              ),
			  array(
                  'value'     => 370,
                  'label'     => Mage::helper('seocontent')->__('Leheriya Sarees'),
              ),
			  array(
                  'value'     => 371,
                  'label'     => Mage::helper('seocontent')->__('Bollywood Sarees'),
              ),
			  array(
                  'value'     => 372,
                  'label'     => Mage::helper('seocontent')->__('Net Sarees'),
              ),
			  array(
                  'value'     => 373,
                  'label'     => Mage::helper('seocontent')->__('Satin Sarees'),
              ),
			  array(
                  'value'     => 374,
                  'label'     => Mage::helper('seocontent')->__('Silk Sarees'),
              ),
			  array(
                  'value'     => 375,
                  'label'     => Mage::helper('seocontent')->__('Wedding Sarees'),
              ),
			  array(
                  'value'     => 781,
                  'label'     => Mage::helper('seocontent')->__('Ragini Sarees'),
              ),
			 
          ),

      ));
      

     /* $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('seocontent')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('seocontent')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('seocontent')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('seocontent')->__('Disabled'),
              ),
          ),
      ));*/
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('seocontent')->__('Content'),
          'title'     => Mage::helper('seocontent')->__('Content'),
          'style'     => 'width:400px; height:400px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getSeocontentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSeocontentData());
          Mage::getSingleton('adminhtml/session')->setSeocontentData(null);
      } elseif ( Mage::registry('seocontent_data') ) {
          $form->setValues(Mage::registry('seocontent_data')->getData());
      }
      return parent::_prepareForm();
  }
}