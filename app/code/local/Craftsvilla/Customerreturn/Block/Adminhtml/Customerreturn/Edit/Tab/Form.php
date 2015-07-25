<?php

class Craftsvilla_Customerreturn_Block_Adminhtml_Customerreturn_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('customerreturn_form', array('legend'=>Mage::helper('customerreturn')->__('Item information')));
     
      $fieldset->addField('shipment_id', 'text', array(
          'label'     => Mage::helper('customerreturn')->__('Shipment Id'),
          'name'      => 'shipment_id',
		  'required'  => true,	
      ));
	 $fieldset->addField('trackingcode', 'text', array(
      	  'label'     => Mage::helper('customerreturn')->__('Tracking Code'),
          'name'      => 'trackingcode',
		  'required'  => true,	
      ));
      $fieldset->addField('couriername', 'text', array(
      	  'label'     => Mage::helper('customerreturn')->__('Courier Name.'),
          'name'      => 'couriername',
		  'required'  => true,	
      ));
	/*$fieldset->addField('created_at', 'date', array(
      	  'label'     => Mage::helper('customerreturn')->__('Created Date'),
          'name'      => 'created_at',
          'format' 	  => 'yyyy-MM-dd',
          'image' 	  => $this->getSkinUrl('images/grid-cal.gif'), 
		  'input_format' => Varien_Date::DATE_INTERNAL_FORMAT, 
          'required'  => true,
      ));*/
			
     // $fieldset->addField('status', 'select', array(
        ///  'label'     => Mage::helper('web')->__('Status'),
        //  'name'      => 'status',
        //  'values'    => array(
         //     array(
                //  'value'     => 1,
                //  'label'     => Mage::helper('web')->__('Enabled'),
             /////////// ),

              //array(
            //      'value'     => 2,
             //     'label'     => Mage::helper('customerreturn')->__('Disabled'),
             // ),
        //  ),
      //));
     
      $fieldset->addField('remark', 'editor', array(
          'name'      => 'remark',
          'label'     => Mage::helper('customerreturn')->__('Remark'),
          'title'     => Mage::helper('customerreturn')->__('Remark'),
          'style'     => 'width:300px; height:150px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
      if (Mage::getSingleton('adminhtml/session')->getCustomerreturnData())
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomerreturnData());
          Mage::getSingleton('adminhtml/session')->setCustomerreturnData(null);
      } elseif ( Mage::registry('customerreturn_data') ) {
          $form->setValues(Mage::registry('customerreturn_data')->getData());
      }
      return parent::_prepareForm();
  }
}
