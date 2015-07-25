<?php

class Craftsvilla_Managemkt_Block_Adminhtml_Managemkt_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'managemkt';
        $this->_controller = 'adminhtml_managemkt';
        
        $this->_updateButton('save', 'label', Mage::helper('managemkt')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('managemkt')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('managemkt_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'managemkt_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'managemkt_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('managemkt_data') && Mage::registry('managemkt_data')->getId() ) {
            return Mage::helper('managemkt')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('managemkt_data')->getTitle()));
        } else {
            return Mage::helper('managemkt')->__('Add Item');
        }
    }
}