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

class AW_Helpdeskultimate_Block_Customer_Messages extends Mage_Customer_Block_Account_Dashboard
{
    const TEMPLATE_PATH = 'helpdeskultimate/messages.phtml';
    public function __construct()
    {
        $this->setTemplate(self::TEMPLATE_PATH);
        if ($this->getRequest()->getParam('id', false)) {
            $this->setTicket(Mage::getModel('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id', false)));
        } else {
            $this->setTicket(Mage::getModel('helpdeskultimate/ticket'));
        }
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function _getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('helpdeskultimate/message')->getCollection();
            $this->_collection
                    ->addTicketFilter($this->getTicket()->getId());
        }
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_getCollection();
    }

    public function dateFormat($date)
    {
        $_dateFormat = "" .
                $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) .
                " " .
                $this->formatTime($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
                ;
        return $_dateFormat;
    }


    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getTicketId()
    {
        return $this->_collection->getHelpdeskMessageId();
    }

    public function getTicketTitle()
    {
        return $this->_collection->getTicket()->getTitle();
    }

    public function getSupportTitle()
    {
        return $this->getTicket();
    }

    public function getBackUrl()
    {
        return Mage::getUrl('helpdeskultimate/customer/');
    }

    /**
     * Formats content to be fine displayed
     * @param object $Message
     * $Message is AW_Helpdeskultimate_Model_Message or AW_Helpdeskultimate_Model_Ticket
     * @return
     */
    public function getContent($Message)
    {
        $cnt = $Message->getParsedContent();
        $isStripTagAttributes = true;
        if ($Message instanceof AW_Helpdeskultimate_Model_Message && !$Message->isDepartmentReply()) {
            $__instance = 'text';
        }
        elseif($Message instanceof AW_Helpdeskultimate_Model_Ticket && $Message->getCreatedBy() != 'admin') {
            $__instance = 'text';
        }
        else {
             $__instance = 'html';
             $isStripTagAttributes = false;
        }
        $cnt = Mage::getModel('helpdeskultimate/data_parser')->getInstance($__instance)->setText($cnt)->prepareToDisplay($isStripTagAttributes)->getText();
        return $cnt;
    }

    /**
     * Formats content to be inserted like quote
     * @param object $Message
     * @return
     */
    public function getQuoteContent($Message) {
        $cnt = $Message->getData('content');
        if ($Message instanceof AW_Helpdeskultimate_Model_Message && !$Message->isDepartmentReply()) {
            $__instance = 'text';
        }
        elseif($Message instanceof AW_Helpdeskultimate_Model_Ticket && $Message->getCreatedBy() != 'admin') {
            $__instance = 'text';
        }
        else {
             $__instance = 'html';
        }
        $cnt = Mage::getModel('helpdeskultimate/data_parser')->getInstance($__instance)->setText($cnt)->convertToQuoteAsText()->getText();
        return $cnt;
    }

    /**
     * Retrives User Can Quote flag
     * @return boolean
     */
    public function getCanQuote()
    {
        return ($this->getTicket()->getStatus() != AW_Helpdeskultimate_Model_Status::STATUS_CLOSED);
    }

    protected function _beforeToHtml()
    {
        $this->_getCollection()
                ->orderBy('created_time DESC')
                ->load();
        return parent::_beforeToHtml();
    }

    public function isExternal()
    {
        return !!$this->getRequest()->getParam('uid');
    }
}
