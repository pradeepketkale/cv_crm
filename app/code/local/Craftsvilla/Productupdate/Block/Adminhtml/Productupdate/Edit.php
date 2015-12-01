<?php

class Craftsvilla_Productupdate_Block_Adminhtml_Productupdate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'productupdate';
        $this->_controller = 'adminhtml_productupdate';
        
        $this->_updateButton('save', 'label', Mage::helper('productupdate')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('productupdate')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
          $this->_addButton('reindex', array(
    'label'     => Mage::helper('productupdate')->__('reindex'),
	'onclick'   => "reindex()",
	'type' => 'button',
    //'class'     => 'save',
    ));
	  

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productupdate_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productupdate_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productupdate_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('productupdate_data') && Mage::registry('productupdate_data')->getId() ) {
            return Mage::helper('productupdate')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('productupdate_data')->getTitle()));
        } else {
            return Mage::helper('productupdate')->__('Add Item');
        }
    }
}
