<?php

class Craftsvilla_Refundrequestedcustomer_Block_Adminhtml_Refundrequestedcustomer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'refundrequestedcustomer';
        $this->_controller = 'adminhtml_refundrequestedcustomer';
        
        $this->_updateButton('save', 'label', Mage::helper('refundrequestedcustomer')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('refundrequestedcustomer')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('refundrequestedcustomer_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'refundrequestedcustomer_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'refundrequestedcustomer_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('refundrequestedcustomer_data') && Mage::registry('refundrequestedcustomer_data')->getId() ) {
            return Mage::helper('refundrequestedcustomer')->__("Edit Refund Request '%s'", $this->htmlEscape(Mage::registry('refundrequestedcustomer_data')->getTitle()));
        } else {
            return Mage::helper('refundrequestedcustomer')->__('Add Refund Request');
        }
    }
}
