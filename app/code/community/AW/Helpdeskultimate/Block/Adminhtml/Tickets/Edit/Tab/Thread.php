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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Thread extends Mage_Adminhtml_Block_Template
{
    protected $_mode;

    public function __construct()
    {
        parent::__construct();
        $this->setId('helpdeskMessagesGrid');
        $this->setTemplate('helpdeskultimate/ticket/edit/thread.phtml');
        $this->_label = Mage::helper('helpdeskultimate')->__("Ticket Thread");
        $this->_headLabel = Mage::helper('helpdeskultimate')->__("Ticket Information");
    }

    public function setDisplayMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    public function getRemoveMessageUrl($ticketId, $messageId) {
        return $this->getUrl('*/*/removemessage', array('id' => $ticketId, 'mid' => $messageId));
    }

    public function getSaveMessageBodyUrl() {
        return $this->getUrl('*/*/savemessagebody');
    }

    public function getParserForMessage($Message)
    {
        if ($this->isDepartmentAuthor($Message)) {
            $__instance = 'html';
        }
        else {
            $__instance = 'text';
        }
        return Mage::getModel('helpdeskultimate/data_parser')->getInstance($__instance);
    }

    public function isDepartmentAuthor($Message)
    {
        if ($Message instanceof AW_Helpdeskultimate_Model_Message && $Message->isDepartmentReply()) {
            return true;
        }
        elseif($Message instanceof AW_Helpdeskultimate_Model_Ticket && $Message->getCreatedBy() == 'admin') {
            return true;
        }
        return false;
    }

    protected function _getAuthorHtml($name = null)
    {
        $ticket = $this->getCollection()->getTicket();
        return $name ? $name : $ticket->getInitiator()->getName();
    }

    function insertUrls($text)
    {
        return Mage::helper('helpdeskultimate/threads')->processLinks($text);
    }

    /**
     * Formats date
     * @param string $dt
     * @return string
     */
    protected function DTFormat($dt)
    {
        return $this->formatDate($dt, 'medium', true);
    }


}
