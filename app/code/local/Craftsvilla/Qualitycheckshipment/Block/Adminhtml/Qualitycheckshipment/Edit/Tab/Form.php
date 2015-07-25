<?php

class Craftsvilla_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('qualitycheckshipment_form', array('legend'=>Mage::helper('qualitycheckshipment')->__('Item information')));
     protected function _prepareCollection()
	{
      $collection = Mage::getModel('sales/order_shipment')->getCollection()->setOrder('increment_id', 'DESC');
      $this->setCollection($collection);
      return parent::_prepareCollection();
	}
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('sales')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('qualitycheckshipment')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('qualitycheckshipment')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('qualitycheckshipment')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('qualitycheckshipment')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('qualitycheckshipment')->__('Content'),
          'title'     => Mage::helper('qualitycheckshipment')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getQualitycheckshipmentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQualitycheckshipmentData());
          Mage::getSingleton('adminhtml/session')->setQualitycheckshipmentData(null);
      } elseif ( Mage::registry('qualitycheckshipment_data') ) {
          $form->setValues(Mage::registry('qualitycheckshipment_data')->getData());
      }
      return parent::_prepareForm();
  }
}
