<?php

class Craftsvilla_Disputeraised_Block_Adminhtml_Disputeraised_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'disputeraised';
        $this->_controller = 'adminhtml_Disputeraised';
        
        $this->_updateButton('save', 'label', Mage::helper('disputeraised')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('disputeraised')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('disputeraised_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'disputeraised_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'disputeraised_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('disputeraised_data') && Mage::registry('disputeraised_data')->getId() ) {
            return Mage::helper('disputeraised')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('disputeraised_data')->getTitle()));
        } else {
            return Mage::helper('disputeraised')->__('Add Item');
        }
    }
}
