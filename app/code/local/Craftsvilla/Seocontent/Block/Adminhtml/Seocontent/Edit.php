<?php

class Craftsvilla_Seocontent_Block_Adminhtml_Seocontent_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'seocontent';
        $this->_controller = 'adminhtml_seocontent';
        
        $this->_updateButton('save', 'label', Mage::helper('seocontent')->__('Save Content'));
        $this->_updateButton('delete', 'label', Mage::helper('seocontent')->__('Delete Content'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('seocontent_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'seocontent_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'seocontent_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('seocontent_data') && Mage::registry('seocontent_data')->getId() ) {
            return Mage::helper('seocontent')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('seocontent_data')->getTitle()));
        } else {
            return Mage::helper('seocontent')->__('Add Content');
        }
    }
}