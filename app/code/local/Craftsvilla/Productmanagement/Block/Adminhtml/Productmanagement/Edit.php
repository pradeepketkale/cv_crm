<?php

class Craftsvilla_Productmanagement_Block_Adminhtml_Productmanagement_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'productmanagement';
        $this->_controller = 'adminhtml_productmanagement';
        
        $this->_updateButton('save', 'label', Mage::helper('productmanagement')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('productmanagement')->__('Delete Item'));
	
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productmanagement_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productmanagement_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productmanagement_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('productmanagement_data') && Mage::registry('productmanagement_data')->getId() ) {
            return Mage::helper('productmanagement')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('productmanagement_data')->getTitle()));
        } else {
            return Mage::helper('productmanagement')->__('Add Item');
        }
    }
}
