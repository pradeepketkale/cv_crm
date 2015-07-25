<?php

class Craftsvilla_Mktvendors_Block_Adminhtml_Mktvendors_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('mktvendors_form', array('legend'=>Mage::helper('mktvendors')->__('Mktpk information')));
     
      $fieldset->addField('package_name', 'select', array(
          'label'     => Mage::helper('mktvendors')->__('Package Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'package_name',
		  'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mktvendors')->__('Rs 1000'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mktvendors')->__('Rs 2000'),
              ),
			  array(
                  'value'     => 3,
                  'label'     => Mage::helper('mktvendors')->__('Rs 5000'),
              ),
			  array(
                  'value'     => 4,
                  'label'     => Mage::helper('mktvendors')->__('Other'),
              ),
          ),

      ));
	  $fieldset->addField('vendor', 'select', array(
          'label'     => Mage::helper('mktvendors')->__('Vendor Id'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'vendor',
		  'type' => 'options',
          'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
          'filter' => 'udropship/vendor_gridColumnFilter'
      ));
	
	$fieldset->addField('paidamount', 'text', array(
          'label'     => Mage::helper('mktvendors')->__('Paid Amount'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'paidamount',
      ));
	  $fieldset->addField('balance', 'text', array(
          'label'     => Mage::helper('mktvendors')->__('Balance Amount'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'balance',
      ));
	  
	  $fieldset->addField('date_bought', 'date', array(
      	  'label'     => Mage::helper('mktvendors')->__('Bought Date'),
          'name'      => 'date_bought',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  $fieldset->addField('valid_till', 'date', array(
      	  'label'     => Mage::helper('mktvendors')->__('Valid Till'),
          'name'      => 'valid_till',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));
	  
 /*$fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('mktvendors')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mktvendors')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mktvendors')->__('Disabled'),
              ),
          ),
      ));
*/     
      if ( Mage::getSingleton('adminhtml/session')->getMktvendorsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMktvendorsData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('mktvendors_data') ) {
          $form->setValues(Mage::registry('mktvendors_data')->getData());
      }
      return parent::_prepareForm();
  }
}