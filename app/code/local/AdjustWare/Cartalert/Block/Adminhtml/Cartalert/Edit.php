<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.4.1.x-1.5.0.1 - 06/07/11
 * Package:     AdjustWare_Cartalert_3.0.5_0.2.3_183688
 * Purchase ID: Y6M1PHMt9YjaYLDNXoI3HVQQ5WLuo3S19F0xW5tLYM
 * Generated:   2012-02-06 21:31:16
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Cartalert/Edit.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ ZrEkjaDBPamUwDwQ('013670464b93953794539a7b013158e6'); ?><?php

class AdjustWare_Cartalert_Block_Adminhtml_Cartalert_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_cartalert';

        $this->_removeButton('reset');
		
        $this->_addButton('send', array(
            'label'     => Mage::helper('adjcartalert')->__('Save and Send Out'),
            'onclick'   => 'sendAndDelete()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function sendAndDelete(){
                $('edit_form').action += 'send/edit';
                editForm.submit();
            }
        ";
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Abandoned Cart Alert');
    }
} } 