<?php

class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('mktngproducts_form', array('legend'=>Mage::helper('mktngproducts')->__('Product information')));
     
      $fieldset->addField('product_sku', 'text', array(
          'label'     => Mage::helper('mktngproducts')->__('Product SKU'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'product_sku',
      ));
	  $fieldset->addField('fb_post_id', 'text', array(
          'label'     => Mage::helper('mktngproducts')->__('Fb Post Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'fb_post_id',
      ));
      /*$fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('mktngproducts')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('mktngproducts')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mktngproducts')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mktngproducts')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('mktngproducts')->__('Content'),
          'title'     => Mage::helper('mktngproducts')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));*/
     
      if ( Mage::getSingleton('adminhtml/session')->getMktngproductsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMktngproductsData());
          Mage::getSingleton('adminhtml/session')->setMktngproductsData(null);
      } elseif ( Mage::registry('mktngproducts_data') ) {
          $form->setValues(Mage::registry('mktngproducts_data')->getData());
      }
      return parent::_prepareForm();
  }
}
