<?php

class Craftsvilla_Disputecusremarks_Block_Adminhtml_Disputecusremarks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'disputecusremarks';
        $this->_controller = 'adminhtml_disputecusremarks';
        
        $this->_updateButton('save', 'label', Mage::helper('disputecusremarks')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('disputecusremarks')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('disputecusremarks_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'disputecusremarks_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'disputecusremarks_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('disputecusremarks_data') && Mage::registry('disputecusremarks_data')->getId() ) {
            return Mage::helper('disputecusremarks')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('disputecusremarks_data')->getTitle()));
        } else {
            return Mage::helper('disputecusremarks')->__('Add Item');
        }
    }
}
