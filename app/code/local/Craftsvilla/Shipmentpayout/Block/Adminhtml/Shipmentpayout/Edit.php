<?php

class Craftsvilla_Shipmentpayout_Block_Adminhtml_Shipmentpayout_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shipmentpayout';
        $this->_controller = 'adminhtml_shipmentpayout';
        
        //$this->_updateButton('save', 'label', Mage::helper('Craftsvilla_Shipmentpayout')->__('Save Item'));
        $this->_updateButton('save', 'label', Mage::helper('shipmentpayout')->__('Save Shipmentpayout'));
        $this->_updateButton('delete', 'label', Mage::helper('shipmentpayout')->__('Delete Shipmentpayout'));
        //$this->_updateButton('delete', 'label', Mage::helper('Craftsvilla_Shipmentpayout')->__('Delete Item'));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipmentpayout_data') && Mage::registry('shipmentpayout_data')->getId() ) {
            return Mage::helper('shipmentpayout')->__("Edit Shipment Payout '%s'", $this->htmlEscape(Mage::registry('shipmentpayout_data')->getShipmentId()));
        } else {
            return Mage::helper('shipmentpayout')->__('Add Shipmentpayout');
        }
    }
}
