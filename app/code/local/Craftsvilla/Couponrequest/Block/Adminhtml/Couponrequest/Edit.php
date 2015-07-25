<?php

class Craftsvilla_Couponrequest_Block_Adminhtml_Couponrequest_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'couponrequest';
        $this->_controller = 'adminhtml_couponrequest';
        
        $this->_updateButton('save', 'label', Mage::helper('couponrequest')->__('Save Coupon'));
        $this->_updateButton('delete', 'label', Mage::helper('couponrequest')->__('Delete Coupon'));
		
		
		

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('couponrequest_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'couponrequest_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'couponrequest_content');
                }
            }
			
			
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('couponrequest_data') && Mage::registry('couponrequest_data')->getId() ) {
            return Mage::helper('couponrequest')->__("Edit Coupon '%s'", $this->htmlEscape(Mage::registry('couponrequest_data')->getTitle()));
        } else {
            return Mage::helper('couponrequest')->__('Add Coupon');
        }
    }
}
