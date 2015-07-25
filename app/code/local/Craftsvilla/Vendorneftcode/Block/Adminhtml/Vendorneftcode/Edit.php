<?php

class Craftsvilla_Vendorneftcode_Block_Adminhtml_Vendorneftcode_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'vendorneftcode';
        $this->_controller = 'adminhtml_vendorneftcode';
        
        $this->_updateButton('save', 'label', Mage::helper('vendorneftcode')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('vendorneftcode')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('vendorneftcode_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'vendorneftcode_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'vendorneftcode_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('vendorneftcode_data') && Mage::registry('vendorneftcode_data')->getId() ) {
            return Mage::helper('vendorneftcode')->__("Edit Neft '%s'", $this->htmlEscape(Mage::registry('vendorneftcode_data')->getTitle()));
        } else {
            return Mage::helper('vendorneftcode')->__('Add NEFT Code');
        }
    }
}
