<?php

class Craftsvilla_Noticeboard_Block_Adminhtml_Noticeboard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'noticeboard';
        $this->_controller = 'adminhtml_noticeboard';
        
        $this->_updateButton('save', 'label', Mage::helper('noticeboard')->__('Save Notice'));
        $this->_updateButton('delete', 'label', Mage::helper('noticeboard')->__('Delete Notice'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('noticeboard_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'noticeboard_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'noticeboard_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('noticeboard_data') && Mage::registry('noticeboard_data')->getId() ) {
            return Mage::helper('noticeboard')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('noticeboard_data')->getTitle()));
        } else {
            return Mage::helper('noticeboard')->__('Add Notice');
        }
    }
}