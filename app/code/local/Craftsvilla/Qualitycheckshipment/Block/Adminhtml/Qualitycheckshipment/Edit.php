<?php

class Craftsvilla_Qualitycheckshipment_Block_Adminhtml_Qualitycheckshipment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'qualitycheckshipment';
        $this->_controller = 'adminhtml_qualitycheckshipment';
        
        $this->_updateButton('save', 'label', Mage::helper('qualitycheckshipment')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('qualitycheckshipment')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('qualitycheckshipment_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'qualitycheckshipment_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'qualitycheckshipment_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('qualitycheckshipment_data') && Mage::registry('qualitycheckshipment_data')->getId() ) {
            return Mage::helper('qualitycheckshipment')->__("Ordered Products ", $this->htmlEscape(Mage::registry('qualitycheckshipment_data')->getTitle()));
        } else {
				
            return Mage::helper('qualitycheckshipment')->__('Ordered Products');
        }
    }
}
