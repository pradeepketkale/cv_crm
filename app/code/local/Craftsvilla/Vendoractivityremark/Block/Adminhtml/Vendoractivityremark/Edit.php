<?php

class Craftsvilla_Vendoractivityremark_Block_Adminhtml_Vendoractivityremark_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'vendoractivityremark';
        $this->_controller = 'adminhtml_vendoractivityremark';
        
        $this->_updateButton('save', 'label', Mage::helper('vendoractivityremark')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('vendoractivityremark')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('vendoractivityremark_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'vendoractivityremark_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'vendoractivityremark_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('vendoractivityremark_data') && Mage::registry('vendoractivityremark_data')->getId() ) {
            return Mage::helper('vendoractivityremark')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('vendoractivityremark_data')->getTitle()));
        } else {
            return Mage::helper('vendoractivityremark')->__('Add Item');
        }
    }
}
