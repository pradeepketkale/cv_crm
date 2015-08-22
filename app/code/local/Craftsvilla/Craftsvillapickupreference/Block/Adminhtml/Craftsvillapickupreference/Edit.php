<?php

class Craftsvilla_Craftsvillapickupreference_Block_Adminhtml_Craftsvillapickupreference_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'craftsvillapickupreference';
        $this->_controller = 'adminhtml_craftsvillapickupreference';
        
        $this->_updateButton('save', 'label', Mage::helper('craftsvillapickupreference')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('craftsvillapickupreference')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('craftsvillapickupreference_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'craftsvillapickupreference_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'craftsvillapickupreference_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('craftsvillapickupreference_data') && Mage::registry('craftsvillapickupreference_data')->getId() ) {
            return Mage::helper('craftsvillapickupreference')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('craftsvillapickupreference_data')->getTitle()));
        } else {
            return Mage::helper('craftsvillapickupreference')->__('Add Item');
        }
    }
}
