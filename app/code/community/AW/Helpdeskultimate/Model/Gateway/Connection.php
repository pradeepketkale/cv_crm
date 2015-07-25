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

class AW_Helpdeskultimate_Model_Gateway_Connection extends Varien_Object
{
    const IMAP_ENGINE_CLASS = 'AW_Helpdeskultimate_Model_Gateway_Connection_Imap';
    const POP_ENGINE_CLASS = 'AW_Helpdeskultimate_Model_Gateway_Connection_Pop3';

    /**
     * Initializes service from gateway
     * @param Varien_Object $Gateway Object with all connection params
     * @return
     */
    public function initFromVarienObject(Varien_Object $Gateway)
    {
        $this
                ->setType($Gateway->getProtocol())
                ->setHost($Gateway->getHost())
                ->setLogin($Gateway->getLogin())
                ->setPassword($Gateway->getPassword())
                ->setPort($Gateway->getPort())
                ->setSecure($Gateway->getSecure() == AW_Helpdeskultimate_Model_Gateway::SECURE_NONE ? null
                                    : $Gateway->getSecure());

        $instanceConstructor = $this->_getConnectionConstructor();
        try {
            // Try to connect
            $this->setInstance(new $instanceConstructor($this->_getConnectionParams()));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->log($e->getMessage());
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Initializes service from gateway
     * @param AW_Helpdeskultimate_Model_Gateway $Gateway
     * @return
     */
    public function initFromGateway(AW_Helpdeskultimate_Model_Gateway $Gateway)
    {
        $this
                ->setType($Gateway->getProtocol())
                ->setHost($Gateway->getHost())
                ->setCreateTickets($Gateway->getCreateTickets())
                ->setLogin($Gateway->getLogin())
                ->setGatewayId($Gateway->getId())
                ->setDeleteMessages($Gateway->getDeleteMessage())
                ->setPassword($Gateway->getPassword())
                ->setPort($Gateway->getPort())
                ->setSecure($Gateway->getSecure() == AW_Helpdeskultimate_Model_Gateway::SECURE_NONE ? null
                                    : $Gateway->getSecure())
                ->setGateway($Gateway);


        $instanceConstructor = $this->_getConnectionConstructor();
        try {
            // Try to connect	
            $this->setInstance(new $instanceConstructor($this->_getConnectionParams()));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->log($e->getMessage());
            return false;
        }
        return $this;
    }

    /**
     * Returns parameters for connection
     * @return array
     */
    protected function _getConnectionParams()
    {
        $params = array(
            'host' => $this->getHost(),
            'user' => $this->getLogin(),
            'password' => $this->getPassword()
        );
        if ($this->getPort()) {
            $params['port'] = $this->getPort();
        }
        if ($this->getSecure()) {
            $params['ssl'] = strtoupper($this->getSecure());
        }
        return $params;
    }

    /**
     * Returns new UIDs from mailbox
     * @return array
     */
    public function getNewUIDs()
    {
        if (!$this->getData('new_uids')) {
            $newUIDs = $existingUIDs = array();
            // Get all uids from mailbox
            $mailboxUIDs = $this->getInstance()->getUniqueId();

            $existingUIDs = Mage::getModel('helpdeskultimate/popmessage')->getCollection()
                ->addGatewayIdFilter($this->getGatewayId())
                ->addFieldToSelect('uid')
                ->getColumnValues('uid')
            ;
            $newUIDs = array_diff($mailboxUIDs, $existingUIDs);
            $this->setData('new_uids', $newUIDs);
        }
        return $this->getData('new_uids');
    }

    /**
     * Returns new messages at connection
     * @return
     */
    public function saveNewMessages()
    {
        $new_uids = $this->getNewUIDs();
        $this->log("%d new messages found", $this->getStats()->getCountMessages());
        $_mailCounter = 0;
        foreach ($new_uids as $UID) {
            $this->log("(%d) message UID: \"%s\"", ++$_mailCounter, $UID);
            Mage::helper('helpdeskultimate/logger')->addTab();
            try {
                $number = $this->getInstance()->getNumberByUniqueId($UID);
                $_internalCharset = iconv_get_encoding('internal_encoding');
                iconv_set_encoding('internal_encoding', AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING);
                $Message = $this->getInstance()->getMessage($number);
                iconv_set_encoding('internal_encoding', $_internalCharset);
                /* Save to DB as not processed */

                $status = AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_UNPROCESSED;
                $Popmessage = Mage::getModel('helpdeskultimate/popmessage')
                        ->setUid($UID)
                        ->setFrom($Message->from)
                        ->setTo($this->getGateway()->getEmail())
                        ->setContentType($this->getContentType($Message))
                        ->setDate(now())
                        ->setGatewayId($this->getGatewayId())
                        ->setHeaders($this->getInstance()->getRawHeader($number))
                        ->setBody($this->getMessageBody($Message))
                        ->setStatus($status);
                
                if ((method_exists($Message, 'headerExists') && $Message->headerExists('subject'))
                    || (!method_exists($Message, 'headerExists') && array_key_exists('subject', $Message->getHeaders()))
                ) {
                    $Popmessage->setSubject($Message->subject);
                } else {
                    $Popmessage->setSubject(Mage::helper('helpdeskultimate')->__('No Subject'));
                }
                $this->log("Content-Type: %s", $Popmessage->getContentType());
                $this->log("Subject: %s", $Popmessage->getSubject());

                $attachments = Mage::helper('helpdeskultimate/config')->isAllowedManageFiles()
                                            ? ($this->getAttachment($Message)) : null;
                if ($attachments) {
                    $_attachments = array();
                    $_attachmentNames = array();
                    foreach ($attachments as $attachment) {
                        if (strlen($attachment['content']) / 1024 / 1024 > Mage::helper('helpdeskultimate')->getUploadMaxFileSize()) {
                            $this->log("Attachment can't be saved because \"%s\" is too large", $attachment['filename']);
                        } else {
                            $this->log("Got attachment \"%s\"", $attachment['filename']);
                            $attachment['content'] = @base64_encode($attachment['content']);
                            $_attachments[] = $attachment;
                            $_attachmentNames[] = preg_replace('/([\/*:\?<>\| \\\"\'])+/i', '-', $attachment['filename']);
                        }
                    }
                    if ($_attachments)
                        $Popmessage->setAttachmentName(implode('|', $_attachmentNames));
                    $attachmentDB = Mage::getModel('helpdeskultimate/attachment');
                    $attachmentDB->setData(array(
                                                'uid' => $UID,
                                                'attachments' => $_attachments
                                           ))->save();
                }
                //chekc via reject pattern
                if (($rejPid = Mage::helper('helpdeskultimate/imap')->matchRejectingPatterns($Popmessage))) {
                    $Popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_REJECTED);
                    $Popmessage->setRejPid($rejPid);
                }
                $Popmessage->save();

                //Logging  about new message or rejecting
                if ($Popmessage->getStatus() == AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_REJECTED) {
                    $this->log("Message (ID: %s) has been rejected (pattern #%s)", $Popmessage->getId(), $rejPid);
                }
                else {
                    $this->log("New message (ID: %s) from %s", $Popmessage->getId(), $Popmessage->getFrom());
                }
            } catch (Exception $e) {
                $this->log('Bad message from %s: %s', $Message->from, $e->getMessage());
            }
            Mage::helper('helpdeskultimate/logger')->removeTab();
        }

        return $this;
    }

    public function deleteFromRemoteServerByUid($uid)
    {
        $number = $this->getInstance()->getNumberByUniqueId($uid);
        $this->getInstance()->removeMessage($number);
        return $this;
    }

    /**
     * Returns wich engine to use
     * @return string
     */
    protected function _getConnectionConstructor()
    {
        if (strtolower($this->getType()) == 'imap') {
            return self::IMAP_ENGINE_CLASS;
        } else {
            return self::POP_ENGINE_CLASS;
        }
    }

    public function getAttachment($Message, $dsata = null)
    {
        $data = array();
        // Get first flat part

        if ($Message->isMultipart()) {
            $parts = $Message;
            foreach (new RecursiveIteratorIterator($parts) as $part) {
                $attach = $this->getAttachment($part, $data);
                if ($attach)
                    $data[] = $attach;
            }
        } else {
            $headers = $Message->getHeaders();
            $is_attachment = null;
            foreach ($headers as $name => $value) {
                if (is_array($value)) {
                    $value = implode(";", $value);
                }
                if ($is_attachment = preg_match('/(name|filename)="{0,1}([^;\"]*)"{0,1}/si', $value, $matches)) {
                    break;
                }
            }
            if ($is_attachment) {
                $filename = $matches[2];
                $encodedContent = $Message->getContent();

                // Decoding transfer-encoding
                switch ($transferEncoding = @$headers['content-transfer-encoding']) {
                    case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                        $content = quoted_printable_decode($encodedContent);
                        break;
                    case Zend_Mime::ENCODING_BASE64:
                        $content = base64_decode($encodedContent);
                        break;
                    default:
                        $content = $encodedContent;
                }

                $filename = iconv_mime_decode($filename, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING);
                return array('filename' => $filename, 'content' => $content);
            } else {
                return false;
            }
        }
        return $data;
    }

    /**
     * Returns main mail part
     * @param Zend_Mail_Message $Message
     * @return Zend_Mail_Message
     */
    protected function _getMainPart(Zend_Mail_Message $Message)
    {
        // Get first flat part
        $part = $Message;
        while ($part->isMultipart()) {
            $part = $part->getPart(1);
        }
        return $part;
    }


    /**
     * Returns first part of mail/mailpart content-type. It can be text/html or text/plain and so on
     * @param Zend_Mail_Message $Message
     * @return string
     */
    public function getContentType(Zend_Mail_Message $Message)
    {
        $part = $this->_getMainPart($Message);
        try {
            $headers = $part->getHeaders();
            $content_type = @$headers['content-type'] ? $headers['content-type']
                    : AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;
        } catch (Exception $e) {
            $content_type = AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;
        }
        return strtok($content_type, ';');
    }

    /**
     * Fetches first not multi-part data and decodes it according to headers information
     * @param Zend_Mail_Message $Message
     * @return string
     */
    public function getMessageBody(Zend_Mail_Message $Message)
    {
        // Get first flat part
        $part = $this->_getMainPart($Message);

        $headers = $part->getHeaders();
        $encodedContent = $part->getContent();

        // Decoding transfer-encoding
        switch (strtolower($transferEncoding = @$headers['content-transfer-encoding'])) {
            case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                $content = quoted_printable_decode($encodedContent);
                break;
            case Zend_Mime::ENCODING_BASE64:
                $content = base64_decode($encodedContent);
                break;
            default:
                $content = $encodedContent;
        }

        $content_type = @$headers['content-type'] ? $headers['content-type']
                : AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;

        foreach (explode(";", $content_type) as $headerPart) {
            $headerPart = strtolower(trim($headerPart));
            if (strpos($headerPart, 'charset=') !== false) {
                $charset = preg_replace('/charset=[^a-z0-9\-_]*([a-z\-_0-9]+)[^a-z0-9\-]*/i', "$1", $headerPart);
                return iconv($charset, AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING, $content);
            }
        }
        return $content;
    }

    /**
     * Returns stats for mailbox
     * @return
     */
    public function getStats()
    {
        $stats = new Varien_Object(
                        array(
                              'count_messages' => $this->getInstance()->countMessages()
                        )
                );
        return $stats;
    }

    public function log()
    {
        $args = func_get_args();
        call_user_func_array(array(Mage::helper('helpdeskultimate/logger'), 'log'), array_values($args));
    }
}
