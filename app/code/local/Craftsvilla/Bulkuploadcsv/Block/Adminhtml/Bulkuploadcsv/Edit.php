<?php

class Craftsvilla_Bulkuploadcsv_Block_Adminhtml_Bulkuploadcsv_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'bulkuploadcsv';
        $this->_controller = 'adminhtml_bulkuploadcsv';
        
        $this->_updateButton('save', 'label', Mage::helper('bulkuploadcsv')->__('Save Csv'));
        $this->_updateButton('delete', 'label', Mage::helper('bulkuploadcsv')->__('Delete Csv'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('bulkuploadcsv_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'bulkuploadcsv_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'bulkuploadcsv_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('bulkuploadcsv_data') && Mage::registry('bulkuploadcsv_data')->getId() ) {
            return Mage::helper('bulkuploadcsv')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('bulkuploadcsv_data')->getTitle()));
        } else {
            return Mage::helper('bulkuploadcsv')->__('Add Csv');
        }
    }
}