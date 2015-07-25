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
 * @category   Mage
 * @package    Iksula_Ccavenue
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Ccavenue Standard Front Controller
 *
 * @category   Mage
 * @package    Iksula_Ccavenue
 * @name       Iksula_Ccavenue_StandardController
 * @author     Magento Core Team <core@magentocommerce.com>
*/

require('Rc43.php');

class Iksula_Ccavenue_StandardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Return debug flag
     *
     *  @return  boolean
     */
    public function getDebug ()
    {
        return Mage::getSingleton('ccavenue/config')->getDebug();
    }

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder ()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

    /**
     * When a customer chooses Ccavenue on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
		
        $session = Mage::getSingleton('checkout/session');
        $session->setCcavenueStandardQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('ccavenue')->__('Customer was redirected to Ccavenue')
        );
        //$order->save();
		
        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('ccavenue/standard_redirect')
                ->setOrder($order)
                ->toHtml());
		
        $session->unsQuoteId();
    }

    /**
     *  Success response from Ccavenue
     *
     *  @return	  void
     */
    public function  successAction()
    {
    	$reqArr = explode('?DR=',$this->getRequest()->getrequestUri());	
	$DR = $reqArr[1];
	
     if(isset($DR)) {
		 $DR = preg_replace("/\s/","+",$_GET['DR']);	 	 
	 
	     $secret_key = Mage::getSingleton('ccavenue/config')->getSecretKey(); // Your Secret Key

	 	 $rc4 = new Crypt_RC4($secret_key);
 	     $QueryString = base64_decode($DR);
	     $rc4->decrypt($QueryString);
	     $QueryString = split('&',$QueryString);

	 $response = array();
	 
 
	 foreach($QueryString as $param){
	 	$param = split('=',$param);
		$response[$param[0]] = urldecode($param[1]);
	 }
	}
       if($response['ResponseCode']== 0)
       {
       	$session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCcavenueStandardQuoteId());
        $session->unsCcavenueStandardQuoteId();

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('ccavenue')->__('Customer successfully returned from Ccavenue')
        );
        
	    $order->save();
$order->sendNewOrderEmail();
        $this->_redirect('checkout/onepage/success');
       }
       else
       {
       	$this->_redirect('ccavenue/standard/failure');
       }
    }

   
    /**
     *  Notification Action from Secure Ebs
     *
     *  @param    none
     *  @return	  void
     */
    public function notifyAction ()
    {
        $postData = $this->getRequest()->getPost();

        if (!count($postData)) {
            $this->norouteAction();
            return;
        }

        if ($this->getDebug()) {
            $debug = Mage::getModel('ccavenue/api_debug');
            if (isset($postData['cs2']) && $postData['cs2'] > 0) {
                $debug->setId($postData['cs2']);
            }
            $debug->setResponseBody(print_r($postData,1))->save();
        }

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId(Mage::helper('core')->decrypt($postData['cs1']));
        if ($order->getId()) {
            $result = $order->getPayment()->getMethodInstance()->setOrder($order)->validateResponse($postData);

            if ($result instanceof Exception) {
                if ($order->getId()) {
                    $order->addStatusToHistory(
                        $order->getStatus(),
                        $result->getMessage()
                    );
                    $order->cancel();
                }
                Mage::throwException($result->getMessage());
                return;
            }

            $order->sendNewOrderEmail();

            $order->getPayment()->getMethodInstance()->setTransactionId($postData['transaction_id']);

            if ($this->saveInvoice($order)) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            }
            $order->save();
        }
    }

    /**
     *  Save invoice for order
     *
     *  @param    Mage_Sales_Model_Order $order
     *  @return	  boolean Can save invoice or not
     */
    protected function saveInvoice (Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();
            return true;
        }

        return false;
    }

    /**
     *  Failure response from Ccavenue
     *
     *  @return	  void
     */
    public function failureAction ()
    {
        $errorMsg = Mage::helper('ccavenue')->__('There was an error occurred during paying process.');

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory($order->getStatus(), $errorMsg);
            $order->cancel();
            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();

        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }

}

