<?php

class Craftsvilla_Shipmentpayoutlite_Block_Adminhtml_Shipmentpayoutlite_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shipmentpayoutlite';
        $this->_controller = 'adminhtml_shipmentpayoutlite';
        
        $this->_updateButton('save', 'label', Mage::helper('shipmentpayoutlite')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('shipmentpayoutlite')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('shipmentpayoutlite_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'shipmentpayoutlite_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'shipmentpayoutlite_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipmentpayoutlite_data') && Mage::registry('shipmentpayoutlite_data')->getId() ) {
            return Mage::helper('shipmentpayoutlite')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('shipmentpayoutlite_data')->getTitle()));
        } else {
            return Mage::helper('shipmentpayoutlite')->__('Add Item');
        }
    }
}
