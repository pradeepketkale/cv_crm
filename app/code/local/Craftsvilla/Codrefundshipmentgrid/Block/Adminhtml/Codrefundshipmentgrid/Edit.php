<?php

class Craftsvilla_Codrefundshipmentgrid_Block_Adminhtml_Codrefundshipmentgrid_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'codrefundshipmentgrid';
        $this->_controller = 'adminhtml_codrefundshipmentgrid';
        
        $this->_updateButton('save', 'label', Mage::helper('codrefundshipmentgrid')->__('Save shipment'));
        $this->_updateButton('delete', 'label', Mage::helper('codrefundshipmentgrid')->__('Delete Item'));
		$this->_removeButton('delete');
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('codrefundshipmentgrid_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'codrefundshipmentgrid_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'codrefundshipmentgrid_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('codrefundshipmentgrid_data') && Mage::registry('codrefundshipmentgrid_data')->getId() ) {
            return Mage::helper('codrefundshipmentgrid')->__("Edit Cod Shipment ");
        } else {
            return Mage::helper('codrefundshipmentgrid')->__('Add Codrefundshipment');
        }
    }
}
