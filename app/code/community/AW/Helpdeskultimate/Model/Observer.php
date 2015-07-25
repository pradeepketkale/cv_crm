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

class AW_Helpdeskultimate_Model_Observer
{

    /**
     * Detects if form submitted by bot or not
     * @return true [if bot]
     */
    protected function _isBot()
    {
        if (Mage::app()->getRequest()->getParam('fail_key')) {
            // Auth by fail key
            if ($lc = Mage::getSingleton('customer/session')->getLastBotCheck()) {
                if ((time() - $lc) < 15) {
                    Mage::getSingleton('customer/session')->setLastBotCheck(time());
                    return true;
                } else {
                    Mage::getSingleton('customer/session')->setLastBotCheck(time());
                }
            }
            return !Mage::getSingleton('helpdeskultimate/antibot')->checkFailKey(Mage::app()->getRequest()->getParam('fail_key'), Mage::app()->getRequest()->getParam('fail_key_hash'));
        } else {
            return !Mage::getSingleton('helpdeskultimate/antibot')->checkSeed(Mage::app()->getRequest()->getParam('hdu_seed'));
        }
    }

    /**
     * Checks and creates proto from contact form
     * @return
     */
    public function contactFormBotProtect($observer)
    {
        $request = Mage::app()->getRequest();
        $response = Mage::app()->getResponse();
        if ($request->getParam('antibot-field')) {
            $_configHelper = Mage::helper('helpdeskultimate/config');
            if ($_configHelper->isIntegrationWithContactFormEnabled()) {
                $this->saveContactFormToTicket();
                if ($_configHelper->isStandartContactFormDisabled()) {
                    Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                    $response->setRedirect(Mage::getUrl('*/*/'))->sendResponse();
                    die();
                }
            }
        } else {
            Mage::getSingleton('core/session')->addError(Mage::helper('helpdeskultimate')->__('Antispam protection failed'));
            $observer->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $myController = new AW_Helpdeskultimate_Controller_Action($request, $response);
            $myController->redirectReferer(Mage::getUrl('contacts/index/index'));
            return;
        }
    }

    /**
     * Creates ticket from order
     * @return
     */
    public function createFromOrder()
    {
        // Create proto
        if (Mage::app()->getRequest()->getParam('create_ticket')) {
            $Order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));

            $history = Mage::app()->getRequest()->getPost();
            $history = @$history['history'];

            $Proto = Mage::getModel('helpdeskultimate/proto')
                    ->setSubject(Mage::helper('helpdeskultimate')->__('Order #%s', $Order->getIncrementId()))
                    ->setContent(trim(@$history['comment']))
                    ->setContentType('text/plain')
                    ->setFrom($Order->getCustomerEmail())
                    ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                    ->setStoreId($Order->getStoreId())
                    ->setSource('order')
                    ->setOrderId($Order->getId())
                    ;
            try {
                $Proto->save();
                if ($Proto->canBeConvertedToMessage()) {
                    $Proto->convertToMessage();
                } else {
                    $Proto->convertToTicket();
                }
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch(Exception $e) {
                $this->log("Error occuring when create ticket from order: {$e->getMessage()}");
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * Checks and creates proto from pq form
     * @return
     */
    public function PQFormBotProtect()
    {
        $_configHelper = Mage::helper('helpdeskultimate/config');
        if ($_configHelper->isIntegrationWithPQEnabled()) {
            $this->savePQFormToTicket();
        }
    }


    /**
     * Saves contact form to ticket proto
     * @return
     */
    public function saveContactFormToTicket()
    {
        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            foreach ($post as $k => $v) {
                if (is_string($v)) {
                    $post[$k] = strip_tags($post[$k]);
                }
            }

            try {

                $error = false;
                if (!Zend_Validate::is(trim(@$post['name']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['comment']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['email']), 'EmailAddress')) {
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                return;
            }

            if (@$post['telephone']) {
                @$post['comment'] .= ("\r\n" . Mage::helper('helpdeskultimate')->__('Telephone:') . @$post['telephone']);
            }

            // Create proto
            $Proto = Mage::getModel('helpdeskultimate/proto')
                    ->setSubject(Mage::helper('helpdeskultimate')->__('Contact form %s <%s>', trim(@$post['name']), trim(@$post['email'])))
                    ->setContent(trim(@$post['comment']))
                    ->setContentType('text/plain')
                    ->setFrom(Mage::helper('helpdeskultimate')->__('%s <%s>', trim(@$post['name']), trim(@$post['email'])))
                    ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setSource('contacts')
                    ;
            try {
                $Proto->save();
                if ($Proto->canBeConvertedToMessage()) {
                    $Proto->convertToMessage();
                } else {
                    $Proto->convertToTicket();
                }
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch(Exception $e) {
                $this->log("Error occuring when create ticket from contact form: {$e->getMessage()}");
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * Saves PQ form to ticket
     * @return
     */
    public function savePQFormToTicket()
    {
        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            foreach ($post as $k => $v) {
                $post[$k] = html_entity_decode($v, ENT_QUOTES, AW_Helpdeskultimate_Model_Data_Parser_Abstract::STORAGE_ENCODING);
            }
            try {
                $error = false;
                if (!Zend_Validate::is(trim(@$post['question_author_name']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['question_text']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['question_author_email']), 'EmailAddress')) {
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                return;
            }
            $product = "";
            if (Mage::app()->getRequest()->getParam('id')) {
                $Product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('id'));
                $product = $Product->getName();
            }

            // Create proto
            $Proto = Mage::getModel('helpdeskultimate/proto')
                    ->setSubject(Mage::helper('helpdeskultimate')->__('Product question on product %s from %s <%s>', $product, trim(@$post['question_author_name']), trim(@$post['question_author_email'])))
                    ->setContent(trim(@$post['question_text']))
                    ->setContentType('text/plain')
                    ->setFrom(trim(@$post['question_author_email']))
                    ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setSource('pq')
                    ;

            try {
                $Proto->save();
                if ($Proto->canBeConvertedToMessage()) {
                    $Proto->convertToMessage();
                } else {
                    $Proto->convertToTicket();
                }
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch(Exception $e) {
                $this->log("Error occuring when create ticket from PQ: {$e->getMessage()}");
                $Proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * If customer is deleted - close ticket and unassign any customer from ticket
     * @param object $event
     * @return
     */
    public function unlinkCustomerTickets($event)
    {
        $Customer = $event->getCustomer();
        foreach (Mage::getModel('helpdeskultimate/ticket')->getCollection()->addCustomerFilter($Customer->getId()) as $ticket) {
            $ticket
                    ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED)
                    ->setCustomerId(0)->save();
        }
    }

    public function log($message)
    {
        Mage::helper('awcore/logger')->log($this, 'Observer', null, $message);
        return $this;
    }
}
