<?php

class Craftsvilla_Agentpayout_Block_Adminhtml_Agentpayout_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'agentpayout';
        $this->_controller = 'adminhtml_agentpayout';
        
        $this->_updateButton('save', 'label', Mage::helper('agentpayout')->__('Save Agentpayout'));
        $this->_updateButton('delete', 'label', Mage::helper('agentpayout')->__('Delete Agentpayout'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('agentpayout_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'agentpayout_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'agentpayout_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('agentpayout_data') && Mage::registry('agentpayout_data')->getId() ) {
            return Mage::helper('agentpayout')->__("Edit Payout '%s'", $this->htmlEscape(Mage::registry('agentpayout_data')->getTitle()));
        } else {
            return Mage::helper('agentpayout')->__('Add Payout');
        }
    }
}