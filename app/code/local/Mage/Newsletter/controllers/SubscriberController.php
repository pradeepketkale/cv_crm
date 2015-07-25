<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter subscribe controller
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_SubscriberController extends Mage_Core_Controller_Front_Action
{
    /**
      * New subscription action
      */
    public function _newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        $this->_redirectReferer();
    }
	
	public function newAction()
{
    if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
        $session   = Mage::getSingleton('core/session');
        $email     = (string) $this->getRequest()->getPost('email');
		
		//added for google adwords 18-10-11
		
		/**
                        *  OpenX image beacon tracker code
                        *  - Generated with OpenX v2.8.2-rc25
                        *
                        *  If this tag is being served on a secure (SSL) page, you must replace
                        *  'http://d1.openx.org/...'
                        * with
                        *  'https://d1.openx.org/...'
                        *
                        *  To help prevent caching of this tracker beacon, if possible,
                        *  Replace %%RANDOM_NUMBER%% with a randomly generated number (or timestamp)
                        *
                        *  In order for the adserver to track variables for this conversion,
                        *  they must be provided by the client.
                        *
                        *  Additional variables may be added, however they must be added
                        *  in the adserver as well before they will be logged.
                        *
                        *  The '%%VARIABLE_VALUE%%' should be replaced with the
                        *  actual values for this sale.
                        **/
		$timestamp    = time();
		$openXtracker = "<div id='m3_tracker_1828' style='position: absolute; left: 0px; top: 0px; visibility: hidden;'><img src='http://d1.openx.org/ti.php?trackerid=1828&amp;email=$email&amp;cb=$timestamp' width='0' height='0' alt='' /></div>";
								   
		// EuroAds Tracking code
		$euroadsTracker = "<iframe src='http://www.euroads.dk/system/showtrackingpixels.php?cpid=2125&sid=1&orderid=$email&pgid=96' width='1' height='1' marginwidth='0' marginheight='0' allowtransparency='true' frameborder='0' scrolling='no' hspace='0' vspace='0'></iframe>";
		//end

        try {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                Mage::throwException($this->__('Please enter a valid email address'));
            }

            $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
            if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                $session->addSuccess($this->__('Confirmation request has been sent'));
            }
            else {
                $session->addSuccess($this->__('Thank you for your subscription'));
            }

                //ADD COUNTRY INFO START

                //at this point we may safly assume that subscription record was created
                //let's retrieve this record and add the additional data to it
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

                //assuming that the input's id is "country"
                $subscriber->setCountry((string) $this->getRequest()->getPost('country'));

                //don't forget to save the subscriber!
                $subscriber->save();

                //ADD COUNTRY INFO END
        }
        catch (Mage_Core_Exception $e) {
            $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('There was a problem with the subscription'));
        }
    }
    $this->_redirectReferer();
}

    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your subscription has been confirmed.'));
                } else {
                    $session->addError($this->__('Invalid subscription confirmation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * Unsubscribe newsletter
     */
    public function unsubscribeAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $session = Mage::getSingleton('core/session');
            try {
                Mage::getModel('newsletter/subscriber')->load($id)
                    ->setCheckCode($code)
                    ->unsubscribe();
                $session->addSuccess($this->__('You have been unsubscribed.'));
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $e->getMessage());
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the un-subscription.'));
            }
        }
        $this->_redirectReferer();
    }
}
