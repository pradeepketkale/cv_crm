<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @version    2.9.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function getHeaderText()
    {
        $_hduTicketData = Mage::registry('hdu_ticket_data');
        if ($_hduTicketData && $_hduTicketData->getId()) {
            return $this->__("Post Reply to '%s' [#%s]", $this->escapeHtml($_hduTicketData->getTitle()), $_hduTicketData->getUid());
        } else {
            return $this->__('Create New Ticket');
        }
    }

    public function getBackUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/');
    }

    /*CONSTRUCT*/
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'helpdeskultimate';
        $this->_mode = 'edit';
        $this->_controller = 'adminhtml_tickets';

        $this->_updateButton('save', 'label', $this->__('Save'));

        $this->_addButton('saveandemail', array(
                                               'label'   => $this->__('Save and email'),
                                               'onclick' => 'saveAndEmail()',
                                               'class'   => 'save',
                                          ), -100);

        $this->_addButton('saveandcontinue', array(
                                                  'label'   => $this->__('Save And Continue Edit'),
                                                  'onclick' => 'saveAndContinueEdit()',
                                                  'class'   => 'save',
                                             ), -100);
        $this->_formScripts[] = "
            if ($('customer_id') && ($('customer_id').options.length > 0)){
                for(var i = 0; i < $('customer_id').options.length; i++){
                   $('customer_id').options[i].text = $('customer_id').options[i].text.replace('&lt;', '<');
                   $('customer_id').options[i].text = $('customer_id').options[i].text.replace('&gt;','>');
                }
            }
            if ($('department_saved_id') && ($('department_saved_id').options.length > 0)){
                for(var i = 0; i < $('department_saved_id').options.length; i++){
                   $('department_saved_id').options[i].text = $('department_saved_id').options[i].text.replace('&lt;', '<');
                   $('department_saved_id').options[i].text = $('department_saved_id').options[i].text.replace('&gt;','>');
                }
            }
            ";

        if (Mage::registry('hdu_ticket_data')->isReadOnly()) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('saveandcontinue');
            $this->_removeButton('saveandemail');
            $this->_removeButton('reset');
        } else {
            $this->_formScripts[] = "
                function saveAndEmail(){
                    $('edit_form').email.value = 1;
                    editForm.submit($('edit_form').action);
                }

                function saveAndContinueEdit(){
                    editForm.submit($('edit_form').action+'back/edit/');
                }

                function toggleEditor() {
                    if (tinyMCE.getInstanceById('helpdeskultimate_content') == null) {
                        tinyMCE.execCommand('mceAddControl', false, 'helpdeskultimate_content');
                    } else {
                        tinyMCE.execCommand('mceRemoveControl', false, 'helpdeskultimate_content');
                    }
                }

                //variables for JS in skin/adminhtml/[package]/[themes]/aw_helpdeskultimate/js/ticket.js
                var AWHDUAjaxFindOrderUrl = '" . Mage::getSingleton('adminhtml/url')->getUrl('*/ticket/ajaxfindorder') . "';
                var AWHDUCurrentTicketId = '" . Mage::registry('hdu_ticket_data')->getId() . "';
                var AWHDUUrlToOrderView =  '" . Mage::getUrl('adminhtml/sales_order/view') . "';
                var AWHDUUrlToUserSuggest = '" . Mage::getSingleton('adminhtml/url')->getUrl('*/ticket/usersuggest') . "';

                $('templates_button').onclick = function(){
                    if($('quick_template').selectedIndex){
                  new Ajax.Request('{$this->getUrl('*/ticket/ajaxgettplcontent')}id/'+$('quick_template').getValue(),
                  {
                    method:'get',
                    onSuccess: function(transport){
                      var response = transport.responseText || '';
                        if (tinyMCE.get(wysiwygcontent_value.id)) {
                            wysiwygcontent_value.toggle();
                            insertatcursor($('content_value'), response);
                            wysiwygcontent_value.toggle();
                        }
                        else {
                            insertatcursor($('content_value'), response);
                        }
                    }
                  });
                  }else{
                alert('" . $this->__('Please select template') . "')
                    }
                }

                function insertatcursor(myField, myValue) {

                    if (document.selection) {
                        myField.focus();
                        sel = document.selection.createRange();
                        sel.text = myValue;
                    }
                    else if (myField.selectionStart || myField.selectionStart == '0') {
                        var startPos = myField.selectionStart;
                        var endPos = myField.selectionEnd;
                        myField.value = myField.value.substring(0, startPos)+ myValue+ myField.value.substring(endPos, myField.value.length);
                    }
                    else {
                        myField.value += myValue;
                    }
                }

                setTimeout(function(){
                    $('content_value').focus();
                }, 500);
            ";
        }
            $this->_formScripts[] = "
                var AWHDUMessageBlockquoteOpenTitle = '" . $this->__('Click to open quotation') . "';
                var AWHDUMessageBlockquoteCloseTitle = '" . $this->__('Click to close quotation') . "';
            ";
    }
}
