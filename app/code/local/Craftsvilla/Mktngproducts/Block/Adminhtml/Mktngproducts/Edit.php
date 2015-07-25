<?php

class Craftsvilla_Mktngproducts_Block_Adminhtml_Mktngproducts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mktngproducts';
        $this->_controller = 'adminhtml_mktngproducts';
        
        $this->_updateButton('save', 'label', Mage::helper('mktngproducts')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('mktngproducts')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('mktngproducts_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'mktngproducts_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'mktngproducts_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('mktngproducts_data') && Mage::registry('mktngproducts_data')->getId() ) {
            return Mage::helper('mktngproducts')->__("Edit Marketing Product '%s'", $this->htmlEscape(Mage::registry('mktngproducts_data')->getTitle()));
        } else {
            return Mage::helper('mktngproducts')->__('Add Marketing Product');
        }
    }
}
