<?php

class Craftsvilla_Codshipments_Block_Adminhtml_Codshipments_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
   /* public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'codshipments';
        $this->_controller = 'adminhtml_codshipments';
        
        $this->_updateButton('save', 'label', Mage::helper('codshipments')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('codshipments')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('web_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'web_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'web_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }*/

    /* public function getHeaderText()
    {
       if( Mage::registry('codshipments_data') && Mage::registry('codshipments_data')->getId() ) {
            return Mage::helper('codshipments')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('codshipments_data')->getTitle()));
        } else {
            return Mage::helper('codshipments')->__('Add Item');
        }
    }*/
}