<?php

class Craftsvilla_Productdownloadreq_Block_Adminhtml_Productdownloadreq_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'productdownloadreq';
        $this->_controller = 'adminhtml_productdownloadreq';
        
        $this->_updateButton('save', 'label', Mage::helper('productdownloadreq')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('productdownloadreq')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productdownloadreq_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productdownloadreq_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productdownloadreq_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('productdownloadreq_data') && Mage::registry('productdownloadreq_data')->getId() ) {
            return Mage::helper('productdownloadreq')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('productdownloadreq_data')->getTitle()));
        } else {
            return Mage::helper('productdownloadreq')->__('Add Item');
        }
    }
}