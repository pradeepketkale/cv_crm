<?php

class Craftsvilla_Sellerqualitycraftsvilla_Block_Adminhtml_Sellerqualitycraftsvilla_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'sellerqualitycraftsvilla';
        $this->_controller = 'adminhtml_sellerqualitycraftsvilla';
        
        $this->_updateButton('save', 'label', Mage::helper('sellerqualitycraftsvilla')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sellerqualitycraftsvilla')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('sellerqualitycraftsvilla_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'sellerqualitycraftsvilla_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'sellerqualitycraftsvilla_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('sellerqualitycraftsvilla_data') && Mage::registry('sellerqualitycraftsvilla_data')->getId() ) {
            return Mage::helper('sellerqualitycraftsvilla')->__("Edit Seller '%s'", $this->htmlEscape(Mage::registry('sellerqualitycraftsvilla_data')->getTitle()));
        } else {
            return Mage::helper('sellerqualitycraftsvilla')->__('Add Seller');
        }
    }
}
