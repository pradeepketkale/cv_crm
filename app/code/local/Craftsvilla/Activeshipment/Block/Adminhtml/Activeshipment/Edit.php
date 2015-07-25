<?php

class Craftsvilla_Activeshipment_Block_Adminhtml_Activeshipment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'activeshipment';
        $this->_controller = 'adminhtml_activeshipment';
        
        $this->_updateButton('save', 'label', Mage::helper('activeshipment')->__('Save Shipment'));
        $this->_updateButton('delete', 'label', Mage::helper('activeshipment')->__('Delete Shipment'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('activeshipment_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'activeshipment_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'activeshipment_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('activeshipment_data') && Mage::registry('activeshipment_data')->getId() ) {
            return Mage::helper('activeshipment')->__("Edit Shipment '%s'", $this->htmlEscape(Mage::registry('activeshipment_data')->getTitle()));
        } else {
            return Mage::helper('activeshipment')->__('Add Shipment');
        }
    }
}