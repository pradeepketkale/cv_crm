<?php

class Marketplace_Feedback_VendorController extends Mage_Core_Controller_Front_Action
{

	public function feedbackInfoAction()
	{

	   $this->loadLayout(false);
	   $block = $this->getLayout()->getBlock('info');
	   $this->getResponse()->setBody($block->toHtml());
	}
		
	public function imageShowAction()
	{
		$this->loadLayout(false);
		$block = $this->getLayout()->getBlock('imageshow');
		$this->getResponse()->setBody($block->toHtml());
	}
	public function feedbackSaveAction()
	{
		$Feedback = '';
		$Feedback = Mage::getModel('feedback/vendor_feedback');
		$r = $this->getRequest();
		$_shipmentId = $r->getParam('shippment_id');
        	$_vendorId = $r->getParam('vendor_id');
		$_received = $r->getParam('received');
		$_rating = $r->getParam('rating');
		$_feedback = $r->getParam('feedback');
		$_feedbackAt =  Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$_customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$uploader = '';
		$image = '';
		if($_FILES['upload-image-file']['name'] != ''){
			$exts= '';
			$n = '';
			$file_size= '';
			$uploader = new Varien_File_Uploader('upload-image-file');
			$exts = explode(".", $_FILES['upload-image-file']['name']) ;
			$n = count($exts)-1;
			$exts = strtolower($exts[$n]);
			if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;

                }

                $file_size = $_FILES['upload-image-file']['size'];
                if($file_size > 5242880) {
                        echo "2";
                        exit;
                }
			$uploader->setAllowedExtensions(array('jpg','jpeg','png','gif'));
			$uploader->setAllowRenameFiles(true);
			$uploader->setFilesDispersion(true);
			$path = Mage::getBaseDir('media') . DS . 'feedback';
			$uploader->save($path, $_FILES['upload-image-file']['name']);
			$image = $uploader->getUploadedFileName();
		}
		
		$Feedback->setFeedbackAt($_feedbackAt)
				->setImagePath($image)
				->setShipmentId($_shipmentId)
				->setVendorId($_vendorId)
				->setCustId($_customerId)
				->setFeedback($_feedback)
				->setReceived($_received)
				->setRating($_rating)
				->save();
		$this->_forward('feedbackInfo');
	}
    public function shipmentPostAction()
    {
		$session = Mage::getSingleton('customer/session');
        $feedback_update_validate=1;
        $feedback_at =  Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        $id = $r->getParam('ship_id');
        $item_id = $r->getParam('id');
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
        $shipment_items = Mage::getModel('sales/order_shipment_item')->load($item_id);
        $status = $r->getParam('status');
        $Feedback = Mage::getModel('feedback/vendor_feedback');
        
        if($shipment_items->getFeedbackId())
        {
        $feedback_update_validate=0;
        
        $updatefeedback = $Feedback->load($shipment_items->getFeedbackId() ,'feedback_id' );
        
        if($updatefeedback->getFeedback()!=1)
        $feedback_update_validate=0;
        }
        
        //echo $shipment->getUdropshipVendor();
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
        $rating = $this->getRequest()->getParam('feedback');
	//print_r($this->getRequest()->getParams());
	//echo "validation:-" .$feedback_update_validate;
	
        if (!$item_id) {
            return;
        }
        try {
            $store = $shipment->getOrder()->getStore();
                if (in_array( $rating , array('-1', '0' , '1') )) {
                    if ( $status == '' && $feedback_update_validate==1 ) {
                       $Feedback->setVendorId($this->getRequest()->getParam('vendor_id'))
                       ->setCustId($this->getRequest()->getParam('customer_id'))
                       ->setFeedbackComments($this->getRequest()->getParam('detail'))
                       ->setFeedback($this->getRequest()->getParam('feedback'))
                       ->setShipmentId($id)
                       ->setShipItemId($item_id)
                       ->setOrderItemId($shipment_items->getOrderItemId()) 
                       ->setStatus( $this->getRequest()->getParam('feedback') == '-1' || '0'  ?  0 : 1 )
                       ->setCustomerComments($this->getRequest()->getParam('resolution'))
                       ->setHold(0);
                      
                       
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $this->__('%s Feedback Done with  %s', $vendor->getVendorName() , $this->getRequest()->getParam('feedback'))
                        );
                        $Feedback->save();
                        $shipment_items->setFeedbackId($Feedback->getVendorFeedbackId())->save();
                        $shipment->save();
                        $session->addSuccess('Feedback was succesfully created');
                    }
                    elseif ($status == 0 && $feedback_update_validate==0 ) {
echo "update";
                    $id = $shipment_items->getFeedbackId();
			$data = array( 'feedback' => $this->getRequest()->getParam('feedback') , 
			'feedback_comments'=> $this->getRequest()->getParam('detail') ,
			'customer_comments'=> $this->getRequest()->getParam('resolution') ,
			'vendor_id'=> $this->getRequest()->getParam('vendor_id') ,
			'status'=> ($this->getRequest()->getParam('feedback') == 1 ?  1 : 0 ), 
			'hold' => ($this->getRequest()->getParam('feedback') ==  1 ? 1 : 0 ));

		   $feed= Mage::getModel('feedback/feedback');
	           $updatefeedback = $feed->load($id);
                       
			try {
       		             $updatefeedback->setId($id)->addData($data)->save();
                             echo "Data updated successfully.";
 
                             } catch (Exception $e){
                                echo $e->getMessage();
                        }
                         $session->addSuccess('Feedback was succesfully updated');
                    }
                    else {
                    echo "CANT change the feedbacks";
                    //$session->addError("Feedback is already existing");
                    }
            }
            } 
           catch (Exception $e) {
           $session->addError($e->getMessage());
        }
                
        $this->_forward('shipmentInfo');
    }

    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('udropship/vendor_wysiwyg_form_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }
/****Below two actions added by dileswar on dated 18-12-2013  pendinginfo & sendorderebslink******/	
	public function pendingInfoAction()
	{
		$this->loadLayout(false);
		$block = $this->getLayout()->getBlock('pendinginfo');
		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function sendOrderebslinkAction()
		{
			$orderEntityId = $this->getRequest()->getParam('orderId');
			$payOrderData = Mage::getModel('sales/order')->getCollection();
			$payOrderData->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
			    ->where('main_table.entity_id = "'.$orderEntityId.'" AND a.address_type = "billing"');
			//echo $_payOrderData->getSelect()->__toString();
			//echo '<pre>';print_r($_payOrderData->getData());exit;
			$status = 'holded';		
			$comment = 'Customer has created the Ebslink from his Account Page';
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
			$address = rip_tags($address1);
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
						$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$entityid."','".$ebslinkinvoiceId."')");
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
//Payu payment link


public function sendOrderpayulinkAction()
		{
 require_once(Mage::getBaseDir().DS.'payu.php');			
$orderEntityId = $this->getRequest()->getParam('orderId');
			$payOrderData = Mage::getModel('sales/order')->getCollection();
			$payOrderData->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
			    ->where('main_table.entity_id = "'.$orderEntityId.'" AND a.address_type = "billing"');
			//echo $_payOrderData->getSelect()->__toString();
			//echo '<pre>';print_r($_payOrderData->getData());exit;
			$status = 'holded';		
			$comment = 'Customer has created the PayuLink from his Account Page';
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
			$address = rip_tags($address1);
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
					$payuref = $myvar4;
					
					$key =	Mage::getStoreConfig('payment/payucheckout_shared/key');
					$salt =	Mage::getStoreConfig('payment/payucheckout_shared/salt');
					$debug_mode =	Mage::getStoreConfig('payment/payucheckout_shared/debug_mode');


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
						//echo 'Ebslink Email & SMS Sent Successfully! ';
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
					}
					
					curl_close($ch);
					Mage::getModel('ebslink/ebslink')->sendebslink($incrementid);	
					
		
			}
		
// a new function added To Cancel Order		
		public function cancelcodorderAction()
		{ 
		$session = Mage::getSingleton('customer/session');
		$session->getMessagesBlock();
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'cancel_email_to_seller_from_customer_email_template';
		$sender = Array('name'  => 'Craftsvilla',
				'email' => 'customercare@craftsvilla.com');
		$emailCanceled = Mage::getModel('core/email_template');
		$orderEntityId = $this->getRequest()->getParam('orderId');
		$shipmentData = Mage::getModel('sales/order_shipment')->load($orderEntityId);
		$_shipmentId = $shipmentData->getIncrementId();
		$dropship = Mage::getModel('udropship/vendor')->load($shipmentData->getUdropshipVendor());
		$vendorId = $dropship->getVendorId();
		$vendorName = $dropship->getVendorName();
		$vendorEmail = $dropship->getEmail();
		if($shipmentData->getUdropshipStatus() == 11){
		$shipmentData->setUdropshipStatus(6);
		$shipmentData->save();
		$commentAdd = Mage::getModel('sales/order_shipment_comment')
					->setParentId($orderEntityId)
					->setComment('The order has been cancelled by Customer')
					->setCreatedAt(NOW())
					->save();
		$shipmentData->addComment($commentAdd);
		$vars = array('shipmentid'=>$_shipmentId,
					'vendorShopName'=>$vendorName,
					);
		$emailCanceled->sendTransactional($templateId, $sender,$vendorEmail, '', $vars, $storeId);
		$session->addSuccess('Shipment Was Succesfully Canceled');
		$this->_redirect('sales/order/history');
		}
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





