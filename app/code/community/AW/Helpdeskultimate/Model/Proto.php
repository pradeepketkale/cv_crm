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

class AW_Helpdeskultimate_Model_Proto extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';

    protected static $_gateways_departments = null;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/proto');
        $this->_initGatewaysDepartments();
    }

    /**
     * Indicates if protot can be converted to ticket message
     * @return bool
     */
    public function canBeConvertedToMessage()
    {
        return $this->_loadTicket() instanceof AW_Helpdeskultimate_Model_Ticket;
    }

    /**
     * Converts proto to message from tickets
     * @return
     */
    public function convertToMessage()
    {
        $_notify_department = $_notify_customer = 0;
        $Message = Mage::getModel('helpdeskultimate/message');
        if ($Ticket = $this->_loadTicket()) {

            // Detect author of message
            if ($Department = $this->_findDepartment($this->getFromEmail(), $Ticket->getStoreId())) {
                // Message from department
                if ($Ticket->getDepartmentId() != $Department->getId()) {
                    // Ticket is taken by other department
                    $Ticket->setDepartment($Department);
                    $this->log("Ticket #%s is reassigned to department \"%s\"", $Ticket->getUid(), $Department->getName());
                    Mage::helper('helpdeskultimate/notify')->ticketReassigned($Ticket);
                }
                $Ticket
                        ->setIsChangedByCustomer(false)
                        ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_WAITING)
                        ->save();
                $Customer = $Ticket->getCustomer();
                $Message
                        ->setAuthorName($Department->getName())
                        ->setDepartmentId($Department->getId());
                $_notify_customer = 1;
            } else {
                // Found ticket
                $Customer = $this->getCustomer();
                // Message from customer
                $Message
                        ->setAuthorName($Customer->getName())
                        ->setCustomerId($Customer->getId());

                $Ticket
                        ->setIsChangedByCustomer(true)
                        ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_OPEN)
                        ->save();
                $_notify_department = 1;
            }

            $Message
                    ->setFromEmail(1)
                    ->setTicketId($Ticket->getId())
                    ->setContent($this->getContent())
                    ->setContentType($this->getContentType())
                    ->save();

            $Ticket->setTotalReplies($Ticket->getMessagesCount());
            $Ticket->save();

            if ($this->getFilename()) {
                @mkdir($Message->getFolderName());
                foreach (explode('|', $this->getFilename()) as $file) {
                    $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
                    if ($file)
                        copy($this->getFolderName() . $file, $Message->getFolderName() . $file);
                }
                $Message->setFilename($this->getFilename())->save();
            }

            if ($_notify_department) {
                Mage::helper('helpdeskultimate/notify')->ticketReplyToAdmin($Message);
            }
            if ($_notify_customer) {
                Mage::helper('helpdeskultimate/notify')->ticketReplyToCustomer($Message, $Customer);
            }
        } else {
            throw new AW_Core_Exception("Can't find ticket for proto #%s while converting to ticket", $this->getId());
        }

        return $Message;
    }

    /**
     * Converts proto to new ticket
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function convertToTicket()
    {
        $Ticket = Mage::getModel('helpdeskultimate/ticket');
        $Ticket
                ->setContent($this->getContent())
                ->setPriority($this->getPriority())
                ->setContentType($this->getContentType())
                ->setTitle($this->getSubject())
                ->setOrderId($this->getOrderId());

        if (($Department = $this->_findDepartment($this->getFromEmail())) && ($this->getSource() == 'order' || $this->getSource() == 'email') ) {
            // Ticket is initiated by department
            $Ticket->setCreatedBy(AW_Helpdeskultimate_Model_Ticket::CREATED_BY_ADMIN);
        } else {
            // Ticket is initiated by customer
            $Ticket->setCreatedBy(AW_Helpdeskultimate_Model_Ticket::CREATED_BY_CUSTOMER);
            if (!$this->getDepartmentId()) {
                $Department = $this->getDefaultDepartment();
            } else {
                $Department = Mage::getModel('helpdeskultimate/department')->load($this->getDepartmentId());
            }
            $Ticket->setCustomer($this->getCustomer());
        }

        /**
         * detect store id
         */
        $Ticket->setStoreId(0);
        if ($this->getStoreId()) {
            $Ticket->setStoreId($this->getStoreId());
        }
        elseif(!Mage::app()->isSingleStoreMode() && $Department->getPrimaryStoreId()) {
            $Ticket->setStoreId($Department->getPrimaryStoreId());
            $this->log("Assigned ticket to store #%s", $Department->getPrimaryStoreId());
        }

        $Ticket
                ->setDepartment($Department)->save();
        $this->log("Assigned ticket #%s to department \"%s\"", $Ticket->getUid(), $Department->getName());

        if ($this->getFilename()) {
            @mkdir($Ticket->getFolderName());
            foreach (explode('|', $this->getFilename()) as $file) {
                $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
                if ($file)
                    copy($this->getFolderName() . $file, $Ticket->getFolderName() . $file);
            }
            $Ticket->setFilename($this->getFilename())->save();
        }

        // Notifying 
        Mage::helper('helpdeskultimate/notify')->ticketNew($Ticket, $Ticket->getCustomer());
        return $Ticket;
    }

    /**
     * Tryes to resolve department by email. If not found return bool false
     * @param string $email
     * @return AW_Helpdeskultimate_Model_Department|bool false
     */
    protected function _findDepartment($email, $storeId = null)
    {
        $email = strtolower($email);
        $departments = Mage::getModel('helpdeskultimate/department')->getCollection()->setContactFilter($email)->load();

        foreach ($departments as $item) {
            if ($item->getEnabled() && ($storeId === null || $storeId == $item->getPrimaryStoreId())) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Returns gateway.
     * @return AW_Helpdeskultimate_Model_Gateway
     */
    public function getGateway()
    {
        if (!$this->getData('gateway')) {
            Mage::getModel('helpdeskultimate/gateway')->load($this->getGatewayId());
        }
        return $this->getData('gateway');
    }

    /**
     * Tryes to guess department
     * @return AW_Helpdeskultimate_Model_Department
     */
    public function getDefaultDepartment()
    {

        if ($this->getGatewayId()) {
            if (isset(self::$_gateways_departments[$this->getGatewayId()])) {
                //return first department for this gateway
                $data = self::$_gateways_departments[$this->getGatewayId()];
                return Mage::getModel('helpdeskultimate/department')->load($data[0]);
            } else {
                foreach (Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter() as $Department) {
                    return $Department;
                }
            }
        } else {
            // No gateway id. Try to restore by store id
            $dep = Mage::getModel('helpdeskultimate/department')->loadByPrimaryStoreId($this->getStoreId());
            if ($dep->getId()) {
                return $dep;
            } else {
                foreach (Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter() as $Department) {
                    return $Department;
                }
            }
        }
        throw new AW_Core_Exception("Failed to get any department.");
        $this->log("No departments found for gateway #%s", $this->getGatewayId());
        return Mage::getModel('helpdeskultimate/department');
    }

    /**
     * Returns founded customer or preset entity
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getCustomer()
    {
        return $this->getSourceInstance()->getCustomer($this);
    }

    /**
     * Returns parsed email
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getFromEmail()
    {
        return $this->getSourceInstance()->getFromEmail($this);
    }

    /**
     * Initializes gateway=>department links
     * @return array
     */
    protected function _initGatewaysDepartments()
    {
        if (!self::$_gateways_departments) {
            $gateways = array();
            $global_deps = array();
            $departments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
            foreach ($departments as $Department) {
                if (!$Department->usesAllGateways()) {
                    foreach ($Department->getGateways() as $gw) {
                        @$gateways[$gw][] = $Department->getId();
                    }
                } else {
                    //department uses all gateways
                    $global_deps[] = $Department->getId();
                }
            }
            if (sizeof($global_deps)) {
                foreach (Mage::getModel('helpdeskultimate/gateway')->getCollection()->addActiveFilter() as $Gateway) {
                    foreach ($global_deps as $gd_id) {
                        @$gateways[$Gateway->getId()][] = $gd_id;
                    }
                }
            }
            self::$_gateways_departments = $gateways;
        }
    }

    /**
     * Tryes to load ticket
     * @return AW_Helpdeskultimate_Model_Ticket | bool false
     */
    protected function _loadTicket()
    {
        if (!$this->getData('_ticket') && $this->getData('_ticket') !== false) {
            $this->setData('_ticket', false);
            if (!($uid = $this->_parseTicketUid($this->getSubject()))) {
                return false;
            }
            if ($Ticket = Mage::getModel('helpdeskultimate/ticket')->loadByUid($uid)) {
                if ($Ticket->getId()) {
                    $this->setData('_ticket', $Ticket);
                    return $Ticket;
                }
            }
        }

        return $this->getData('_ticket');
    }

    /**
     * Returns ticket UID from subject
     * @param string $subject
     * @return string|bool
     */
    protected function _parseTicketUid($subject)
    {
        if (preg_match("/\[#([a-z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            return strtoupper(@$matches[1]);
        } else
            return false;
    }

    /**
     * Returns folder for according proto
     * @return string
     */
    public function getFolderName()
    {
        $path = Mage::getBaseDir('media') . DS . 'helpdeskultimate' . DS . 'proto-' . $this->getId() . DS;
        return $path;
    }

    /**
     * Save filename
     * @return string
     */
    public function getFilename()
    {
        // Override to clean up zombie files
        if (isset($this->_data['filename'])) {
            $fn = $this->_data['filename'];
        } else {
            return '';
        }
        foreach (explode('|', $fn) as $file) {
            $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
            if ($file)
                return $fn;
        }
        return '';
    }

    /**
     * Returns source instance
     * @return AW_Helpdeskultimate_Model_Proto_Source_Abstract
     */
    public function getSourceInstance()
    {
        if (!$this->getData('source_instance')) {
            $this->setData('source_instance', Mage::getModel('helpdeskultimate/proto_source_' . $this->getSource()));
        }
        return $this->getData('source_instance');
    }

    /**
     * Validates proto
     * @return array | bool
     */
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('helpdeskultimate');

        if (!Zend_Validate::is($this->getSubject(), 'NotEmpty')) {
            $errors[] = $helper->__('Please specify title<br/>');
        }

        if (!Zend_Validate::is($this->getContent(), 'NotEmpty')) {
            $errors[] = $helper->__('Content can\'t be empty');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    private function log()
    {
        $args = func_get_args();
        call_user_func_array(array(Mage::helper('helpdeskultimate/logger'), 'log'), array_values($args));
        return $this;
    }
}
