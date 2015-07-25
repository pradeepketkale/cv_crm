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

class AW_Helpdeskultimate_Model_Cron
{
    const LOCK_CACHE_INDEX = 'aw_helpdeskultimate_lock';
    const LOG_DATE_FORMAT  = 'Y-m-d H:i:s';
    const HDU_LOCK_TIMEOUT = 1800;
    const SECONDS_IN_DAY   = 84600;

    protected $_jobs = array(
        //getting new mails from mailboxes and save it in DB as unprocessed mail
        '_fetchMailsFromGateways' => array('startLogMessage' => '[Fetching new mails]'),
        //getting unprocessed mails from DB and save it as pending Proto
        '_processNewMails'        => array('startLogMessage' => '[Making prototypes from messages]'),
        //getting pending Proto from DB and try convert it to ticket or message of ticket
        '_processPendingProto'    => array('startLogMessage' => '[Creating tickets by prototypes]'),
        //closes expired tickets
        '_closeExpiredTickets'    => array('startLogMessage' => '[Closing expired tickets]'),
    );
    protected $_savedConnections = array();

    public function runJobs()
    {
        $this->_helper('logger')->clearLogMemory();
        $this->_log("Cron execution starts [%s]", date(self::LOG_DATE_FORMAT));
        if (self::checkLock()) {
            $this->processJobs();
            Mage::app()->removeCache(self::LOCK_CACHE_INDEX);
        } else {
            $_lastExecTime = date(self::LOG_DATE_FORMAT, Mage::app()->loadCache(self::LOCK_CACHE_INDEX));
            $this->_log("Extension cron job is locked in %s", $_lastExecTime);
        }
        $this->_log("")->_log("Cron execution ends [%s]", date(self::LOG_DATE_FORMAT));
        $this->_helper('logger')->releaseLog("Cron execution");
    }

    /**
     * Checks if one HDU is already running
     * @return
     */
    public static function checkLock()
    {
        $_lastExecutionTime = Mage::app()->loadCache(self::LOCK_CACHE_INDEX);
        if (self::HDU_LOCK_TIMEOUT > (time() - $_lastExecutionTime)) {
            return false;
        }
        Mage::app()->saveCache(time(), self::LOCK_CACHE_INDEX, array(), self::HDU_LOCK_TIMEOUT);
        return true;
    }

 /**
     * Processes whole mailbox
     * @return
     */
    public function processJobs()
    {

        foreach($this->_jobs as $_jobName => $_jobData) {
            //add log message before execute job
            $this->_log("");
            if (isset($_jobData['startLogMessage'])) {
                $this->_log($_jobData['startLogMessage']);
            }
            else {
                $this->_log("Execute job: %s", $_jobName);
            }
            $this->_addTabToLog();
            //execute jobs
            try {
                $this->_executeJob($_jobName);
            }
            catch(Exception $e) {
                $this->_log("Error occurred: %s", $e->getMessage());
            }
            //add log message after execute job
            $this->_removeTabFromLog();
        }
    }

    protected function _executeJob($method)
    {
        $result = call_user_func(array($this, $method));
        return $result;
    }


    /**
     * @return void
     */
    protected function _fetchMailsFromGateways()
    {
        $gatewaysCollection = Mage::getModel('helpdeskultimate/gateway')->getCollection()->addActiveFilter();
        $this->_log("%d gateways found", $gatewaysCollection->getSize());
        $_gateCounter = 0;
        foreach ($gatewaysCollection as $Gateway) {
            $this->_log("(%d) gateway \"%s\"", ++$_gateCounter, $Gateway->getTitle());
            $this->_addTabToLog();
            // Initialize connection
            try {
                $Connection = Mage::getModel('helpdeskultimate/gateway_connection')->initFromGateway($Gateway);
                $this->_log("Connected");
            } catch(Exception $e) {
                $this->_log("Connected failed: %s", $e->getMessage());
            }
            if ($Connection) {
                $this->_savedConnections[$Gateway->getId()] = $Connection;
                try {
                    $Connection->saveNewMessages();
                } catch (Exception $e) {
                    $this->_log($e->getMessage());
                }
            }
            $this->_removeTabFromLog();
        }
    }

    /**
     * @return void
     */
    protected function _processNewMails()
    {
        $popmessagesCollection = Mage::getModel('helpdeskultimate/popmessage')->getCollection()->addUnprocessedFilter();
        $this->_log("%d mailbox messages found", $popmessagesCollection->getSize());
        $_popmessCount = 0;
        foreach ($popmessagesCollection as $Popmessage) {
            $this->_log("(%d) mailbox message ID: %s", ++$_popmessCount, $Popmessage->getId());
            $this->_addTabToLog();
            $protoData = $Popmessage->getDataForProto();
            if ($protoData['from'] == '' || $protoData['content'] == '') {
                $this->_log("Message on %s is skipped. Reason: broken headers.", $protoData['subject']);
            }
            $Proto = Mage::getModel('helpdeskultimate/proto');
            $Proto->setData($protoData);

            if ($this->_helper('config')->isAllowedCreateNewTicketFromEmail() || $Proto->canBeConvertedToMessage()) {
                try {
                    $Proto
                        ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                        ->save();
                    $this->_log("Proto created. ID: %s", $Proto->getId());
                    
                    /* deleting from remote server*/
                    $_conn = $this->_savedConnections[$Popmessage->getGatewayId()];
                    if ($_conn->getDeleteMessages()) {
                        $_conn->deleteFromRemoteServerByUid($Popmessage->getUid());
                        $this->_log("Deleting message #%s from remote server", $Popmessage->getUid());
                    }
                }
                catch(Exception $e) {
                    $this->_log("Proto can not be created: ", $e->getMessage());
                }
            } else {
                $this->_log("New message is skipped. Reason: creating new ticket from email is disabled");
                $Popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_PROCESSED)->save();
                continue;
            }

            // Save attachment if exists
            if ($Popmessage->getAttachmentName()) {
                @mkdir($Proto->getFolderName());
                $attachments = Mage::getModel('helpdeskultimate/attachment')->loadByUid($Popmessage->getUid());
                foreach ($attachments->getData('attachments') as $attach) {
                    $fName = Mage::helper('helpdeskultimate')->getEncodedFileName($attach['filename']);
                    file_put_contents($Proto->getFolderName() . $fName, @base64_decode($attach['content']));
                }
                $attachments->delete();
                $Proto->setFilename($Popmessage->getAttachmentName())->save();
            }

            // Save status for popmessage as processed
            $Popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_PROCESSED)->save();
            $this->_removeTabFromLog();
        }
    }

    /**
     * @return void
     */
    protected function _processPendingProto()
    {
        $pendingProtosCollection = Mage::getModel('helpdeskultimate/proto')->getCollection()->addPendingFilter();
        $this->_log("%d pending prototypes found", $pendingProtosCollection->getSize());
        $_protoCounter =0;
        foreach ($pendingProtosCollection as $Proto) {
            $this->_log("(%d) prototype ID: %s", ++$_protoCounter, $Proto->getId());
            $this->_addTabToLog();
            // Converting protos to tickets/messages
            try {
                if ($Proto->canBeConvertedToMessage()) {
                    $Proto->convertToMessage();
                } else {
                    $Ticket = $Proto->convertToTicket();
                    $this->_log("Ticket created: #%s", $Ticket->getUid());
                }
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            } catch (Exception $e) {
                $this->_log("Error occured: %s", $e->getMessage());
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
            $this->_removeTabFromLog();
        }
    }

    /**
     * Closes expired tickets
     * @return
     * */
    protected function _closeExpiredTickets()
    {
        $_expireTicketInterval = Mage::helper('helpdeskultimate/config')->getTicketExpireAfterDays();
        if ($_expireTicketInterval) {
            $minDate = ( time() - ($_expireTicketInterval * self::SECONDS_IN_DAY) );

            // for each not closed ticket
            $coll = Mage::getModel('helpdeskultimate/ticket')
                    ->getCollection()
                    ->addActiveFilter()
                    ->load();

            $this->_log("%d expired tickets found", $coll->getSize());
            foreach ($coll as $ticket) {

                $messages = Mage::getModel('helpdeskultimate/message')
                        ->getCollection()
                        ->addTicketFilter(
                    $ticket->getId()
                )
                        ->orderBy('created_time DESC')
                        ->load();
                if ($messages->count()) {
                    foreach ($messages as $message) {
                        if ($message->getCreatedTime() && strtotime($message->getCreatedTime()) <= $minDate) {
                            $ticket
                                    ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED)
                                    ->save();
                            $this->_log("Closing ticket %s", $ticket->getUid());
                        }
                        break;
                    }
                } else {
                    if ($ticket->getCreatedTime() && strtotime($ticket->getCreatedTime()) <= $minDate) {
                        $ticket
                                ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED)
                                ->save();
                        $this->_log("Closing ticket %s", $ticket->getUid());
                    }
                }
            }
        }
    }

    protected function _log()
    {
        $args = func_get_args();
        call_user_func_array(array($this->_helper('logger'), 'log'), array_values($args));
        return $this;
    }

    protected function _addTabToLog()
    {
        $this->_helper('logger')->addTab();
        return $this;
    }

    protected function _removeTabFromLog()
    {
        $this->_helper('logger')->removeTab();
        return $this;
    }

    protected function _helper($code = null)
    {
        $_helperName = 'helpdeskultimate' . (!is_null($code)?('/'.$code):'');
        return Mage::helper($_helperName);
    }
}

?>
