<?php

class Craftsvilla_Utrreport_Block_Adminhtml_Utrreport_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'utrreport';
        $this->_controller = 'adminhtml_utrreport';
        
        $this->_updateButton('save', 'label', Mage::helper('utrreport')->__('Save UTR'));
        $this->_updateButton('delete', 'label', Mage::helper('utrreport')->__('Delete UTR'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('utrreport_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'utrreport_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'utrreport_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('utrreport_data') && Mage::registry('utrreport_data')->getId() ) {
            return Mage::helper('utrreport')->__("Edit UTR '%s'", $this->htmlEscape(Mage::registry('utrreport_data')->getTitle()));
        } else {
            return Mage::helper('utrreport')->__('Add UTR');
        }
    }
}