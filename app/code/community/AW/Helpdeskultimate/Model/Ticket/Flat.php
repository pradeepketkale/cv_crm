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

class AW_Helpdeskultimate_Model_Ticket_Flat extends AW_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('helpdeskultimate/ticket_flat');
    }

    /**
     * Presets all ticket info
     * @param AW_Helpdeskultimate_Model_Ticket $Ticket
     * @return AW_Helpdeskultimate_Model_Ticket_Flat
     */
    public function setTicket(AW_Helpdeskultimate_Model_Ticket $Ticket)
    {
        $lastReplyDate = null;
        $customer_reply_processed = $department_reply_processed = 0;
        foreach ($Ticket->getMessages()->setOrder('created_time', 'desc') as $Message) {
            if (!$lastReplyDate) {
                $lastReplyDate = new Zend_Date($Message->getCreatedTime(), AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
            }
            if ($Message->isDepartmentReply() && !$department_reply_processed) {
                $this->setLastDepartmentReply($Message->getCreatedTime());
                $department_reply_processed = 1;
            }
            if (!$Message->isDepartmentReply() && !$customer_reply_processed) {
                $this->setLastCustomerReply($Message->getCreatedTime());
                $customer_reply_processed = 1;
            }
            if ($customer_reply_processed && $department_reply_processed) {
                break;
            }
        }

        $this
                ->setTicketId($Ticket->getId())
                ->setLastReply($lastReplyDate)
                ->setTotalReplies($Ticket->getMessagesCount());
        $Ticket
                ->setLastReply($this->getLastReply())
        //->setTotalReplies($this->getTotalReplies());
                ->setTotalReplies($Ticket->getMessagesCount());
        return $this;
    }


    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastReply()
    {
        return new Zend_Date($this->getData('last_reply'), AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_reply', $date);
        return $this;
    }

    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastDepartmentReply()
    {
        return new Zend_Date($this->getData('last_department_reply'), AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastDepartmentReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_department_reply', $date);
        return $this;
    }


    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastCustomerReply()
    {
        return new Zend_Date($this->getData('last_customer_reply'), AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastCustomerReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_customer_reply', $date);
        return $this;
    }

    public function loadByTicketId($ticketId)
    {
        return $this->load($ticketId, 'ticket_id');
    }
}
