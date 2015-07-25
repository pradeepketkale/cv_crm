<?php

class Craftsvilla_Customerreturn_Block_Adminhtml_Customerreturn_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customerreturn';
        $this->_controller = 'adminhtml_customerreturn';
        
        $this->_updateButton('save', 'label', Mage::helper('customerreturn')->__('Save shipment'));
        $this->_updateButton('delete', 'label', Mage::helper('customerreturn')->__('Delete Item'));
		$this->_removeButton('delete');
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('customerreturn_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'customerreturn_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'customerreturn_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('customerreturn_data') && Mage::registry('customerreturn_data')->getId() ) {
            return Mage::helper('customerreturn')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('customerreturn_data')->getTitle()));
        } else {
            return Mage::helper('customerreturn')->__('Add customerreturn Data');
        }
    }
}
