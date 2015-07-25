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
 
class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    const to_admin_new_email         = 'helpdeskultimate_to_admin_new_email';
    const to_admin_reply_email       = 'helpdeskultimate_to_admin_reply_email';
    const to_customer_new_email      = 'helpdeskultimate_to_customer_new_email';
    const to_customer_reply_email    = 'helpdeskultimate_to_customer_reply_email';
    const to_admin_reassign_email    = 'helpdeskultimate_to_admin_reassign_email';
    const new_from_admin_to_customer = 'helpdeskultimate_new_from_admin_to_customer';

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        $fGeneral = $form->addFieldset('helpdeskultimate_form', array('legend' => $this->__('Department details')));


        $fGeneral->addField('id',         'hidden', array('required' => false, 'name' => 'id'));
        $fGeneral->addField('enabled',    'select', array('label' => $this->__('Active'),     'name' => 'enabled',    'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()));
        $fGeneral->addField('visibility', 'select', array('label' => $this->__('Visibility'), 'name' => 'visibility', 'values' => Mage::getModel('helpdeskultimate/source_visibility')->toOptionArray()));
        $fGeneral->addField('name',       'text',   array('label' => $this->__('Title'),      'name' => 'name',       'required' => true, 'note' => ''));

        if (!Mage::app()->isSingleStoreMode()) {
            $fGeneral->addField('primary_store_id', 'select', array('label' => $this->__('Primary for store'), 'name' => 'primary_store_id', 'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false)));
        }

        $fEmail = $form->addFieldset('helpdeskultimate_form_email', array('legend' => $this->__('Email settings')));
        $fEmail->addField('notify',   'select',      array('label' => $this->__('Use email notifications'), 'name' => 'notify',   'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()));
        $fEmail->addField('contact',  'text',        array('label' => $this->__('Email'),                   'name' => 'contact',  'required' => true, 'note' => 'Emails to department will be sent to this address', 'class' => 'validate-uniq-email'));
        $fEmail->addField('sender',   'select',      array('label' => $this->__('Sender'),                  'name' => 'sender',   'note' => 'This will be used as "From" address in emails sent to department/customer', 'values' => Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray()));
        $fEmail->addField('gateways', 'multiselect', array('label' => $this->__('Use email gateways'),      'name' => 'gateways', 'note' => 'If none gateway is selected, all of them will be checked', 'values' => Mage::getModel('helpdeskultimate/source_gateways')->toOptionArray()));

        $fTemplates = $form->addFieldset('helpdeskultimate_form_templates', array('legend' => $this->__('Email templates')));
        $fTemplates->addField('to_admin_new_email',         'select', array('label' => $this->__('New Ticket Admin Template'),                        'name' => 'to_admin_new_email',         'note' => 'New ticket, notification for support',                              'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::to_admin_new_email)->toOptionArray()));
        $fTemplates->addField('to_admin_reply_email',       'select', array('label' => $this->__('Ticket Reply Admin Template'),                      'name' => 'to_admin_reply_email',       'note' => 'Reply in a ticket, notification for support',                       'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::to_admin_reply_email)->toOptionArray()));
        $fTemplates->addField('to_customer_new_email',      'select', array('label' => $this->__('New Ticket Customer Template'),                     'name' => 'to_customer_new_email',      'note' => 'New ticket, notification for customer',                             'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::to_customer_new_email)->toOptionArray()));
        $fTemplates->addField('new_from_admin_to_customer', 'select', array('label' => $this->__('New Ticket Customer Template(initiated by admin)'), 'name' => 'new_from_admin_to_customer', 'note' => 'New ticket has been created by support, notification for customer', 'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::new_from_admin_to_customer)->toOptionArray()));
        $fTemplates->addField('to_customer_reply_email',    'select', array('label' => $this->__('Ticket Reply Customer Template'),                   'name' => 'to_customer_reply_email',    'note' => 'Reply in a ticket, notification for customer',                      'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::to_customer_reply_email)->toOptionArray()));
        $fTemplates->addField('to_admin_reassign_email',    'select', array('label' => $this->__('Ticket Reassign Template'),                         'name' => 'to_admin_reassign_email',    'note' => 'Ticket reassignation, notification for support',                    'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath(self::to_admin_reassign_email)->toOptionArray()));


        if (Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData());
            Mage::getSingleton('adminhtml/session')->setHelpdeskultimateData(null);
        } elseif (Mage::registry('department')) {

            $form->setValues(Mage::registry('department')->getData());
        }
        return parent::_prepareForm();
    }
}
