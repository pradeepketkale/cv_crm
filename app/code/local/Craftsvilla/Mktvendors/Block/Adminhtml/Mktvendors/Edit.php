<?php

class Craftsvilla_Mktvendors_Block_Adminhtml_Mktvendors_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mktvendors';
        $this->_controller = 'adminhtml_Mktvendors';
        
        $this->_updateButton('save', 'label', Mage::helper('mktvendors')->__('Save Mktpk'));
        $this->_updateButton('delete', 'label', Mage::helper('mktvendors')->__('Delete Mktpk'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('mktvendors_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'mktvendors_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'mktvendors_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('mktvendors_data') && Mage::registry('mktvendors_data')->getId() ) {
            return Mage::helper('mktvendors')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('mktvendors_data')->getTitle()));
        } else {
            return Mage::helper('mktvendors')->__('Add Mktpk');
        }
    }
}