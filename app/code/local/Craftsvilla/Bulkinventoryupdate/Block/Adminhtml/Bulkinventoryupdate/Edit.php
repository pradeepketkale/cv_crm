<?php

class Craftsvilla_Bulkinventoryupdate_Block_Adminhtml_Bulkinventoryupdate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'bulkinventoryupdate';
        $this->_controller = 'adminhtml_bulkinventoryupdate';
        
        $this->_updateButton('save', 'label', Mage::helper('bulkinventoryupdate')->__('Save Csv'));
        $this->_updateButton('delete', 'label', Mage::helper('bulkinventoryupdate')->__('Delete Csv'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('bulkinventoryupdate_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'bulkinventoryupdate_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'bulkinventoryupdate_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('bulkinventoryupdate_data') && Mage::registry('bulkinventoryupdate_data')->getId() ) {
            return Mage::helper('bulkinventoryupdate')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('bulkinventoryupdate_data')->getTitle()));
        } else {
            return Mage::helper('bulkinventoryupdate')->__('Add Csv');
        }
    }
}