<?php

class Craftsvilla_Ebslink_Block_Adminhtml_Ebslink_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'ebslink';
        $this->_controller = 'adminhtml_ebslink';
        
        $this->_updateButton('save', 'label', Mage::helper('ebslink')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('ebslink')->__('Delete Item'));
		
		/*$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);*/
		 
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('ebslink_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'ebslink_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'ebslink_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
		
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('ebslink_data') && Mage::registry('ebslink_data')->getId() ) {
            return Mage::helper('ebslink')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('ebslink_data')->getTitle()));
        } else {
            return Mage::helper('ebslink')->__('ADD Ebslink');
        }
    }
}