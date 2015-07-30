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
 * @package    Mage_Secureebs
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Secureebs Standard Front Controller
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_StandardController
 * @author     Magento Core Team <core@magentocommerce.com>
*/

require('Rc43.php');

class Mage_Secureebs_StandardController extends Mage_Core_Controller_Front_Action
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
        return Mage::getSingleton('secureebs/config')->getDebug();
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
     * When a customer chooses Secureebs on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setSecureebsStandardQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('secureebs')->__('Customer was redirected to Secureebs')
        );
		
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('secureebs/standard_redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

    /**
     *  Success response from Secure Ebs
     *
     *  @return	  void
     */
    public function  successAction()
    {
    	if(isset($_GET['DR'])) {
			 $DR = preg_replace("/\s/","+",$_GET['DR']);	 	 
		 
		     $secret_key = Mage::getSingleton('secureebs/config')->getSecretKey(); // Your Secret Key
	
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
		
	   if(!empty($response) and $response['ResponseCode'] == 0)
       {
			$session = Mage::getSingleton('checkout/session');
	        $session->setQuoteId($session->getSecureebsStandardQuoteId());
	        $session->unsSecureebsStandardQuoteId();
	
	        $order = $this->getOrder();
			if (!$order->getId()) {
	            $this->norouteAction();
	            return;
        	}
        	if($order->hasShipments())
        	{
        		$this->_redirect('checkout/onepage/success');
                        return;
        	}
                else
                {
	        // Code By Mounish To Add Payment Id And Merchant Ref No
			$PaymentID = $response['PaymentID'];
			$MerchantRefNo = $response['MerchantRefNo'];
			$Amount = $response['Amount'];
			$IsFlagged = $response['IsFlagged'];
			$TransactionID = $response['TransactionID'];
			$ResponseCode = $response['ResponseCode'];
			$BillingEmail = $response['BillingEmail'];
			$BillingName = $response['BillingName'];
			$BillingPhone = $response['BillingPhone'];
			
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('catalog_read');
			$w = Mage::getSingleton('core/resource')->getConnection('core_write');
			
			$salesflatorderpaytrackTable = (string)Mage::getConfig()->getTablePrefix() . 'sales_flat_order_payment_ebs';
			$salesflatorderpayhackTable = (string)Mage::getConfig()->getTablePrefix() . 'sales_flat_order_payment_hack_ebs';
			
			$select_orders = $read->select()->from($salesflatorderpaytrackTable)->where("payment_id = ?",$PaymentID);
			$row_orders = $read->fetchRow($select_orders);
			
			if(empty($row_orders)) {
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,'Customer successfully returned from Secureebs',false);
				$order->save();
                            /*else{
                                Mage::log('else');
                                $order->addStatusToHistory($order->getStatus(),'Customer successfully returned from Secureebs');
                                $order->save();
                            }*/
				/*$order->addStatusToHistory(
			        $order->getStatus(),
			        Mage::helper('secureebs')->__('Customer successfully returned from Secureebs')
			    );
				$order->save();*/
				
				$order_max_id = $order->getId();
				$insOrdData = array(
									'order_id' => $order_max_id,
									'payment_id' => $PaymentID,
									'merchant_ref_no' => $MerchantRefNo,
									'order_amount' => $Amount,
									'ebs_is_flagged' => $IsFlagged,
									'transaction_id' => $TransactionID,
									'response_code' => $ResponseCode,
									'payment_method' => 'ebs'
								);
			
				$w->insert('sales_flat_order_payment_ebs', $insOrdData);
				
				//$order->sendNewOrderEmail();
                                
	        	$this->_redirect('checkout/onepage/success');
			
			}
			/*else if(($row_orders['track_id'] > 0) && !empty($row_orders))
			{
				$order = $this->getOrder();
				if($row_orders['order_id'] == $order->getId())
				{
					$this->_redirect('checkout/onepage/success');
				}
				if($order->getStatus() == 'processing')
				{
					$this->_redirect('checkout/onepage/success');
				}
				$insOrdHackData = array(
									'payment_id' => $PaymentID,
									'merchant_ref_no' => $MerchantRefNo,
									'order_amount' => $Amount,
									'ebs_is_flagged' => $IsFlagged,
									'transaction_id' => $TransactionID,
									'response_code' => $ResponseCode,
									'payment_method' => 'ebs',
									'billing_name' => $BillingName,
									'billing_email' => $BillingEmail,
									'billing_phone' => $BillingPhone,
									'order_id'		=> $order->getId(),
									'action_mode'	=> 'track_id > 0'
								);
			
				$w->insert('sales_flat_order_payment_hack_ebs', $insOrdHackData);
				
				$this->_redirect('secureebs/standard/failure');
				
			}
		    */
                }
       }
       else if($response['ResponseCode'] !=  0)
       {
       		$order = $this->getOrder();
       		if($order->getStatus() == 'processing')
       		{
       			$this->_redirect('checkout/onepage/success');
       		}
       		
       		$w = Mage::getSingleton('core/resource')->getConnection('core_write');
       		$PaymentID = $response['PaymentID'];
       		$MerchantRefNo = $response['MerchantRefNo'];
       		$Amount = $response['Amount'];
       		$IsFlagged = $response['IsFlagged'];
       		$TransactionID = $response['TransactionID'];
       		$ResponseCode = $response['ResponseCode'];
       		$BillingEmail = $response['BillingEmail'];
       		$BillingName = $response['BillingName'];
       		$BillingPhone = $response['BillingPhone'];
			$insOrdHackData = array(
								'payment_id' => $PaymentID,
								'merchant_ref_no' => $MerchantRefNo,
								'order_amount' => $Amount,
								'ebs_is_flagged' => $IsFlagged,
								'transaction_id' => $TransactionID,
								'response_code' => $ResponseCode,
								'payment_method' => 'ebs',
								'billing_name' => $BillingName,
								'billing_email' => $BillingEmail,
								'billing_phone' => $BillingPhone,
								'order_id'		=> $order->getId(),
								'action_mode'	=> 'ResponseCode > 0'
							);
		
			$w->insert('sales_flat_order_payment_hack_ebs', $insOrdHackData);
			$this->_redirect('secureebs/standard/failure');
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
            $debug = Mage::getModel('secureebs/api_debug');
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
     *  Failure response from Secureebs
     *
     *  @return	  void
     */
    public function failureAction ()
    {
        //$errorMsg = Mage::helper('secureebs')->__('There was an error occurred during paying process.');
		$errorMsg = Mage::helper('secureebs')->__('Failure Happened During Paying at EBS.');

        $order = $this->getOrder();
		if($order->getStatus() == 'processing')
		{
			$this->_redirect('checkout/onepage/success');
		}
		
        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }
        
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory($order->getStatus(), $errorMsg);
            // commented by dileswar on dated 30-12-2013 tofor the orders not change to cancel.......
			//$order->cancel();
            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();

        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }
	public function sendOrderebslinkAction (){
	
	$incrementFailedId = $this->getRequest()->getParam('orderId');
	$comment = $this->getRequest()->getParam('comment');	
		$order = $this->getOrder();
		$orderData = $order->loadByIncrementId($incrementFailedId);
	    $orderEntityId = $orderData->getEntityId();
		$payOrderData = Mage::getModel('sales/order')->getCollection();
			$payOrderData->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
			    ->where('main_table.entity_id = "'.$orderEntityId.'" AND a.address_type = "billing"');
			//echo $_payOrderData->getSelect()->__toString();
			//echo '<pre>';print_r($_payOrderData->getData());exit;
			$status = 'holded';		
			//$comment = 'Customer has created the payment link from the Payment Failed Page';
			$countHoldOrder = 0;
			$countNonHoldOrder = 0;
			foreach($payOrderData->getData() as $_payOrderData)
			{
			$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
			
			$namecust = $_payOrderData['customer_firstname'];
			$email = $_payOrderData['customer_email'];
			$currencyTotal = Mage::app()->getLocale()->currency($_payOrderData['order_currency_code'])->getSymbol();
			$currency = $_payOrderData['order_currency_code'];
			$grandtotal = $_payOrderData['grand_total'];
			$entityid = $_payOrderData['entity_id'];
			$incrementid = $_payOrderData['increment_id'];
			$_customerTelephone = str_replace('/','',substr($_payOrderData['telephone'],0,10));
			$address1 = $_payOrderData['street'];
			$address = $this->rip_tags($address1);
			$city = $_payOrderData['city'];
			$region = $_payOrderData['region'];
			$postcode = $_payOrderData['postcode'];
			$country_id = $_payOrderData['country_id'];
			$total_qt_ordered = $_payOrderData['total_qty_ordered'];
			$_grandTotal = $grandtotal/$total_qt_ordered;
			$expiry_days = 30;
			//GET ebslinkurlllll
					$url = 'https://secure.ebs.in/api/invoice';
					$myvar1 = 'create';
					$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
					$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
					$myvar4 = $incrementid;
					$myvar5 = $currency;
					$myvar6 = $namecust;
					$myvar7 = $address;
					$myvar8 = $city;
					$myvar9 = $region;
					$myvar10 = $postcode;
					$myvar11 = 'IND';
					$myvar12 = $email;
					$myvar13 = $_customerTelephone;
					$myvar14 = 'Craftsvilla Products';
					$myvar15 = $total_qt_ordered;
					$myvar16 = $_grandTotal;
					$myvar17 = '0';
					$myvar18 = $expiry_days;
					
					
					$fields = array(
									'action' => urlencode($myvar1),
									'account_id' => urlencode($myvar2),
									'secret_key' => urlencode($myvar3),
									'reference_no' => urlencode($myvar4),
									'currency' => urlencode($myvar5),
									'name' => urlencode($myvar6),
									'address' => urlencode($myvar7),
									'city' => urlencode($myvar8),
									'state' => urlencode($myvar9),
									'postal_code' => urlencode($myvar10),
									'country' => urlencode($myvar11),
									'email' => urlencode($myvar12),
									'phone' => urlencode($myvar13),
									'products[0][name]' => urlencode($myvar14),
									'products[0][qty]' => urlencode($myvar15),
									'products[0][price]' => urlencode($myvar16),
									'payment_mode' => urlencode($myvar17),
									'expiry_in_days' => urlencode($myvar18)
									
									);
					$fields_string = '';
					//url-ify the data for the POST
					foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string, '&');
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_VERBOSE, 1); 
					curl_setopt($ch,CURLOPT_URL, $url);
					curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
					curl_setopt( $ch, CURLOPT_POST, 1);
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt( $ch, CURLOPT_HEADER, 0);
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					
					$response = curl_exec($ch);
					if(curl_errno($ch))
					{		
					print curl_error($ch);
					}
					else
					{
					$xml = @simplexml_load_string($response);
					//echo '<pre>';
					//print_r($xml);
					$ebslinkurl = (string)$xml->invoice[0]->payment_url;
					$ebslinkinvoiceId = (string)$xml->invoice[0]->invoice_id;
					//$ebslinkurl = 'http://craftsvilla.com';
					$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
					$readEbslink['ebslinkurl'];
					if($readEbslink) {
						$exeEbslinkUrl = $readEbslink['ebslinkurl'];
						$this->_redirectUrl($exeEbslinkUrl);
						}
					else
						{
						$exeEbslinkUrl = $ebslinkurl;	
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$_orderId."','".$ebslinkinvoiceId."')");
						echo 'Ebslink Email & SMS Sent Successfully! ';
						$orderStatus = Mage::getModel('sales/order')->load($_payOrderData['entity_id']);
						if ($orderStatus->canHold()) 
							{
							$orderStatus->hold()
										->save();
							$countHoldOrder++;
							}
							else 
							{
							$countNonHoldOrder++;
							}
					
					if($countNonHoldOrder)
						{
						if($countHoldOrder)
							{
							echo '<pre>1'.'%s order(s) were not put on hold.', $countNonHoldOrder;
							}
						 else {
							echo '<pre>2'.'No order(s) were put on hold.';
							}
						}
					if ($countHoldOrder)
						 {
							echo '<pre>3'.'%s order(s) have been put on hold.', $countHoldOrder;
						
						 }
					
					Mage::getModel('sales/order_status_history')
							->setParentId($_payOrderData['entity_id'])
							->setStatus($status)
							->setComment($comment)
							->setCreatedAt(NOW())
							->save();
						
					echo '<pre>4'.'save in order & status changed'.$_payOrderData['increment_id'];
						$this->_redirectUrl($exeEbslinkUrl);
							}
					}
					
					curl_close($ch);
					Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
					
		
			}
		}
public function sendOrderpayulinkAction(){
	require_once(Mage::getBaseDir().DS.'payu.php');			
	$incrementFailedId = $this->getRequest()->getParam('orderId');
	$comment = $this->getRequest()->getParam('comment');	
		$order = $this->getOrder();
		$orderData = $order->loadByIncrementId($incrementFailedId);
	    $orderEntityId = $orderData->getEntityId();
		$payOrderData = Mage::getModel('sales/order')->getCollection();
			$payOrderData->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
			    ->where('main_table.entity_id = "'.$orderEntityId.'" AND a.address_type = "billing"');
			//echo $_payOrderData->getSelect()->__toString();
			//echo '<pre>';print_r($_payOrderData->getData());exit;
			$status = 'holded';		
			//$comment = 'Customer has created the payment link from the Payment Failed Page';
			$countHoldOrder = 0;
			$countNonHoldOrder = 0;
			foreach($payOrderData->getData() as $_payOrderData)
			{
			$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
			
			$namecust = $_payOrderData['customer_firstname'];
			$email = $_payOrderData['customer_email'];
			$currencyTotal = Mage::app()->getLocale()->currency($_payOrderData['order_currency_code'])->getSymbol();
			$currency = $_payOrderData['order_currency_code'];
			$grandtotal = $_payOrderData['base_grand_total'];
			$entityid = $_payOrderData['entity_id'];
			$incrementid = $_payOrderData['increment_id'];
			$_customerTelephone = str_replace('/','',substr($_payOrderData['telephone'],0,10));
			$address1 = $_payOrderData['street'];
			$address = $this->rip_tags($address1);
			$city = $_payOrderData['city'];
			$region = $_payOrderData['region'];
			$postcode = $_payOrderData['postcode'];
			$country_id = $_payOrderData['country_id'];
			$total_qt_ordered = $_payOrderData['total_qty_ordered'];
			$_grandTotal = $grandtotal/$total_qt_ordered;
			$expiry_days = 30;
			//GET ebslinkurlllll
					
					$myvar4 = $incrementid.'P';
					$myvar5 = $currency;
					$myvar6 = $namecust;
					$myvar7 = $address;
					$myvar8 = $city;
					$myvar9 = $region;
					$myvar10 = $postcode;
					$myvar11 = 'IND';
					$myvar12 = $email;
					$myvar13 = $_customerTelephone;
					$myvar14 = 'Craftsvilla Products';
					$myvar15 = $total_qt_ordered;
					$myvar16 = $grandTotal;
					$myvar17 = '0';
					$payuref = $myvar4;
					
					
					$key =	Mage::getStoreConfig('payment/payucheckout_shared/key');
					$salt =	Mage::getStoreConfig('payment/payucheckout_shared/salt');
					$debug_mode = Mage::getStoreConfig('payment/payucheckout_shared/debug_mode');

					$valueparam = pay_page( array (	'key' => $key, 'txnid' => $myvar4, 'amount' => round($myvar16,2),
					'firstname' => $myvar6, 'email' => $myvar12, 'phone' => $myvar13,
					'productinfo' => $myvar14, 'surl' => 'payment_success', 'furl' => 'payment_failure'),  $salt);
					// Merchant key here as provided by Payu

					$payulinkurl = $valueparam['data'];

					$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
					$readEbslink['ebslinkurl'];
					if($readEbslink) {
						$exeEbslinkUrl = $readEbslink['ebslinkurl'];
						$this->_redirectUrl($payulinkurl);
						}
					else
						{
						$exeEbslinkUrl = $ebslinkurl;	
						$write = Mage::getSingleton('core/resource')->getConnection('core_write');
						$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$payulinkurl."',NOW(),'".$_orderId."','".$payuref."')");
						echo 'Payu Email & SMS Sent Successfully! ';
						$orderStatus = Mage::getModel('sales/order')->load($_payOrderData['entity_id']);
						if ($orderStatus->canHold()) 
							{
							$orderStatus->hold()
										->save();
							$countHoldOrder++;
							}
							else 
							{
							$countNonHoldOrder++;
							}
					
					if($countNonHoldOrder)
						{
						if($countHoldOrder)
							{
							echo '<pre>1'.'%s order(s) were not put on hold.', $countNonHoldOrder;
							}
						 else {
							echo '<pre>2'.'No order(s) were put on hold.';
							}
						}
					if ($countHoldOrder)
						 {
							echo '<pre>3'.'%s order(s) have been put on hold.', $countHoldOrder;
						
						 }
					
					Mage::getModel('sales/order_status_history')
							->setParentId($_payOrderData['entity_id'])
							->setStatus($status)
							->setComment($comment)
							->setCreatedAt(NOW())
							->save();
						
					echo '<pre>4'.'save in order & status changed'.$_payOrderData['increment_id'];
						$this->_redirectUrl($payulinkurl);
							}
					
					
					curl_close($ch);
					Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
					
		
			}
		}
function rip_tags($string) {

		// ----- remove HTML TAGs -----
		$string = preg_replace ('/<[^>]*>/', ' ', $string);
	
		// ----- remove control characters -----
		$string = str_replace("\r", '', $string);    // --- replace with empty space
		$string = str_replace("\n", ' ', $string);   // --- replace with space
		$string = str_replace("\t", ' ', $string);   // --- replace with space
			$string = str_replace("*", ' ', $string);   // --- replace with space
			$string = str_replace("&", ' ', $string);   // --- replace with space
			$string = str_replace("#", ' ', $string);   // --- replace with space
			$string = str_replace("%", ' ', $string);   // --- replace with space
			$string = str_replace("$", ' ', $string);   // --- replace with space
			$string = str_replace("@", ' ', $string);   // --- replace with space
			$string = str_replace(".com", ' ', $string);   // --- replace with space
			$string = str_replace("?", ' ', $string);   // --- replace with space
			$string = str_replace("=", ' ', $string);   // --- replace with space
			$string = str_replace("-", ' ', $string);   // --- replace with space
			$string = str_replace("+", ' ', $string);   // --- replace with space
			$string = str_replace(",", ' ', $string);   // --- replace with space
			$string = str_replace("!", ' ', $string);   // --- replace with space
			$string = str_replace("\"", ' ', $string);   // --- replace with space
			$string = str_replace("%", ' ', $string);   // --- replace with space
			$string = str_replace("^", ' ', $string);   // --- replace with space
			$string = str_replace("'", ' ', $string);   // --- replace with space
			$string = str_replace("/", ' ', $string);   // --- replace with space
			$string = str_replace("_", ' ', $string);   // --- replace with space
			$string = str_replace("~", ' ', $string);   // --- replace with space
			//$string = str_replace("\", ' ', $string);   // --- replace with space
			//$string = str_replace("\{", ' ', $string);   // --- replace with space
			//$string = str_replace("\}", ' ', $string);   // --- replace with space
			//$string = str_replace("\]", ' ', $string);   // --- replace with space
			//$string = str_replace("\[", ' ', $string);   // --- replace with space
			//$string = str_replace("|", ' ', $string);   // --- replace with space
			$string = str_replace("nbsp", ' ', $string);   // --- replace with space
	
		// ----- remove multiple spaces -----
		$string = trim(preg_replace('/ {2,}/', ' ', $string));
	
		return $string;
		}
	public function addProductTocartAction(){
		$order_id = $this->getRequest()->getParam('entityId');
		$comment = $this->getRequest()->getParam('comment');
		$errorMsg = 'Order has been cancelled by customer to add more producs in failed page';
	
		$order = Mage::getModel("sales/order")->load($order_id); //load order by order id
		// First cancel the order	
		if ($order_id) {
            $order->addStatusToHistory($order->getStatus(), $errorMsg);
            $order->cancel();
            $order->save();
        }
		
		$ordered_items = $order->getAllItems();
		//echo '<pre>';print_r($ordered_items);exit;
		foreach($ordered_items as $item){
		//item detail
			$productid = $item->getProductId(); //product id
			$prdSku = $item->getSku();
			$qty_value = $item->getQtyOrdered(); //ordered qty of item
			$prdName = $item->getName();
			
			$product_model = Mage::getModel('catalog/product');
			$my_product = $product_model->load($productid);
			//echo '<pre>';print_r($my_product);
			//Add to cart code
			$cart = Mage::getModel('checkout/cart');
			$cart->init();
			$cart->addProduct($my_product, array('qty' => $qty_value));
			$cart->save();
			Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
			
			}
		$this->_redirect('checkout/cart');
		}


}

