<?php

class Craftsvilla_Shipmentlite_Block_Adminhtml_Shipmentlite_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shipmentlite';
        $this->_controller = 'adminhtml_Shipmentlite';
        
        $this->_updateButton('save', 'label', Mage::helper('shipmentlite')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('shipmentlite')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('shipmentlite_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'shipmentlite_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'shipmentlite_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipmentlite_data') && Mage::registry('shipmentlite_data')->getId() ) {
            return Mage::helper('shipmentlite')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('shipmentlite_data')->getTitle()));
        } else {
            return Mage::helper('shipmentlite')->__('Add Item');
        }
    }
}
