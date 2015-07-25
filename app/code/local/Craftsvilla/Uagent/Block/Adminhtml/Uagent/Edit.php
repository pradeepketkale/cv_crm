<?php

class Craftsvilla_Uagent_Block_Adminhtml_Uagent_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'uagent';
        $this->_controller = 'adminhtml_uagent';
        
        $this->_updateButton('save', 'label', Mage::helper('uagent')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('uagent')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('uagent_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'uagent_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'uagent_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('uagent_data') && Mage::registry('uagent_data')->getId() ) {
            return Mage::helper('uagent')->__("Edit Uagent Details", $this->htmlEscape(Mage::registry('uagent_data')->getTitle()));
        } else {
            return Mage::helper('uagent')->__('Add Uagent');
        }
    }
}