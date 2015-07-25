<?php

class Craftsvilla_Ebslink_Model_Ebslink extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ebslink/ebslink');
    }
// Model function has written here in below................(Linked to file app/code/local/mage/adminhtml/controllers/sales/OrderController.php)	
	public function sendebslink($orderId)
		{
		$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
		$order = $read->query("SELECT sfo.`entity_id` as entity_id,es.order_no,sfo.`customer_firstname` as custfirstname,es.`ebslinkurl` as ebslinkurl,sfo.`customer_email` as email,sfoa.`telephone`,sfo.`order_currency_code`,sfo.`grand_total` as grandtotal FROM `sales_flat_order` as sfo
					  			LEFT JOIN `ebslink` as es
								ON es.`order_no` = sfo.`increment_id`
								LEFT JOIN `sales_flat_order_address` as sfoa
								ON sfoa.`parent_id` = sfo.`entity_id`
								WHERE es.`order_no`='".$orderId."'")->fetch();
			
			
			/*foreach($order as $_orderData)
			{
			*/
			$_orderData = $order;
			$namecust = $_orderData['custfirstname'];
			$email = $_orderData['email'];
			$currency = Mage::app()->getLocale()->currency($_orderData['order_currency_code'])->getSymbol();
			$grandtotal = $_orderData['grandtotal'];
			$ebslinkurl =  $_orderData['ebslinkurl'];
			$entityid = $_orderData['entity_id'];
			$_customerTelephone = $_orderData['telephone'];
			//}
			
			$orderDataItem = $read->query("SELECT * from `sales_flat_order_item` as sfoi,`sales_flat_order` as sfo WHERE sfoi.`order_id` = sfo.`entity_id` and sfo.`increment_id` = '".$orderId."'")->fetchAll();
				foreach($orderDataItem as $_orderDataitem)
					{	
					$productname = $_orderDataitem['name'];
					$sku =  $_orderDataitem['sku'];
					$subtotal =  $_orderDataitem['base_row_total_incl_tax'];
					$qty = $_orderDataitem['qty_ordered'];
					}
		//sms to customer
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to customer
		$customerMessage = 'Payment Failed for Your Order on Craftsvilla.com. Payment Link Sent to Your Email Id#'.$email.'. Please Try Again to Pay for Your Pending Order - Thanks (Craftsvilla)';
		$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
					$av1 = 'Need Lower Shipping Charges';
					$av2 = 'Looking For COD';
					$av3 = 'Will Pay Later';
					$av4 = 'Need Better Pricing';
					$av5 = 'Not Interested';
					$av6 = 'Need Faster Delivery';
					$av7 = 'Need Details on Return/Refund Policy';
					$av8 = 'Need More Details on Products';
					
				//$action1 = 'http://local.craftsvilla.com/doejofinal/paymentresn.php?q='.$orderId;
				$urlactionday1 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av1;
				$urlactionday2 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av2;
				$urlactionday3 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av3;
				$urlactionday4 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av4;
				$urlactionday5 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av5;
				$urlactionday6 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av6;
				$urlactionday7 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av7;
				$urlactionday8 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av8;
				
		// Ebs Link url to Customer....		
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'ebslinks_email_template';
			$mailSubject = 'Action Required: Payment Link for Your Pending Order on Craftsvilla.com!';
			$sender = Array('name'  => 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
			//$translate  = Mage::getSingleton('core/translate');
			//$translate->setTranslateInline(false);
			$_email = Mage::getModel('core/email_template');
			$button = '<a href ="'.$urlactionday1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Lower Shipping Charges"> Need Lower Shipping Charges</button></a>';
			$button .= '<a href ="'.$urlactionday2.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="1" > Looking For COD</button></a>';
			$button .= '<a href ="'.$urlactionday3.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Will Pay Later" > Will Pay Later</button></a>';
			$button .= '<a href ="'.$urlactionday4.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Better Pricing" > Need Better Pricing</button></a>';
			$button .= '<a href ="'.$urlactionday5.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Not Interested" > Not Interested</button></a>';
			$button .= '<a href ="'.$urlactionday6.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Faster Delivery" > Need Faster Delivery</button></a>';
			$button .= '<a href ="'.$urlactionday7.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Details on Return/Refund Policy" > Need Details on Return/Refund Policy</button></a>';
			$button .= '<a href ="'.$urlactionday8.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="" > Need More Details on Products</button></a>';
			$vars = Array( 'entity_id' => $entityid ,'custfirstname' =>$namecust,'order' => $order,'orderno' =>$orderId,'email'=>$email,'name' => $productname,'grandtotal' =>$currency.$grandtotal,'ebslinkurl' =>$ebslinkurl,'base_row_total_incl_tax' =>$subtotal,'sku' =>$sku, 'qty_ordered'=>$qty, 'action' =>$button,'newgrandtotal'=> $newgrandtotal,
					);
			
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, $email, $namecust, $vars, $storeId);
			
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, "manoj@craftsvilla.com", $namecust, $vars, $storeId);
		
			//$translate->setTranslateInline(true);							
		}
	
	public function sendreviewebslink($orderId,$newgrandTotal,$ebslinkurl,$discountamount)
		{
		$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
		$order = $read->query("SELECT sfo.`entity_id` as entity_id,es.order_no,sfo.`customer_firstname` as custfirstname,es.`ebslinkurl` as ebslinkurl,sfo.`customer_email` as email,sfoa.`telephone`,sfo.`order_currency_code`,sfo.`base_grand_total` as grandtotal FROM `sales_flat_order` as sfo
					  			LEFT JOIN `ebslink` as es
								ON es.`order_no` = sfo.`increment_id`
								LEFT JOIN `sales_flat_order_address` as sfoa
								ON sfoa.`parent_id` = sfo.`entity_id`
								WHERE sfo.`increment_id`='".$orderId."'")->fetch();
			$_orderData = $order;
			$namecust = $_orderData['custfirstname'];
			$email = $_orderData['email'];
			$currency = Mage::app()->getLocale()->currency($_orderData['order_currency_code'])->getSymbol();
			$grandtotal = $_orderData['grandtotal'];
			$entityid = $_orderData['entity_id'];
			$_customerTelephone = $_orderData['telephone'];
			//}
			
			$orderDataItem = $read->query("SELECT * from `sales_flat_order_item` as sfoi,`sales_flat_order` as sfo WHERE sfoi.`order_id` = sfo.`entity_id` and sfo.`increment_id` = '".$orderId."'")->fetchAll();
				foreach($orderDataItem as $_orderDataitem)
					{	
					$productname = $_orderDataitem['name'];
					$sku =  $_orderDataitem['sku'];
					$subtotal =  $_orderDataitem['base_row_total_incl_tax'];
					$qty = $_orderDataitem['qty_ordered'];
					}
		//sms to customer
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to customer
		$customerMessage = 'Revised Payment Link Sent For Your Pending Order on Craftsvilla.com to Your Email Id# '.$email.'. Please Pay for Your Pending Order - Thanks (Craftsvilla)';
		$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
					$av1 = 'Need Lower Shipping Charges';
					$av2 = 'Looking For COD';
					$av3 = 'Will Pay Later';
					$av4 = 'Need Better Pricing';
					$av5 = 'Not Interested';
					$av6 = 'Need Faster Delivery';
					$av7 = 'Need Details on Return/Refund Policy';
					$av8 = 'Need More Details on Products';
					
				//$action1 = 'http://local.craftsvilla.com/doejofinal/paymentresn.php?q='.$orderId;
				$urlactionday1 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av1;
				$urlactionday2 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av2;
				$urlactionday3 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av3;
				$urlactionday4 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av4;
				$urlactionday5 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av5;
				$urlactionday6 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av6;
				$urlactionday7 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av7;
				$urlactionday8 = Mage::getBaseUrl().'umicrosite/vendor/paymentresn?q='.$orderId.'&payment='.$av8;
				
		// Ebs Link url to Customer....		
			$storeId = Mage::app()->getStore()->getId();
			$templateId1 = 'ebslinks_email_revised_template';
			
			$sender = Array('name'  => 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
			//$translate  = Mage::getSingleton('core/translate');
			//$translate->setTranslateInline(false);
			$_email = Mage::getModel('core/email_template');
			$button = '<a href ="'.$urlactionday1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Lower Shipping Charges"> Need Lower Shipping Charges</button></a>';
			$button .= '<a href ="'.$urlactionday2.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="1" > Looking For COD</button></a>';
			$button .= '<a href ="'.$urlactionday3.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Will Pay Later" > Will Pay Later</button></a>';
			$button .= '<a href ="'.$urlactionday4.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Better Pricing" > Need Better Pricing</button></a>';
			$button .= '<a href ="'.$urlactionday5.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Not Interested" > Not Interested</button></a>';
			$button .= '<a href ="'.$urlactionday6.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Faster Delivery" > Need Faster Delivery</button></a>';
			$button .= '<a href ="'.$urlactionday7.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="Need Details on Return/Refund Policy" > Need Details on Return/Refund Policy</button></a>';
			$button .= '<a href ="'.$urlactionday8.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="paymentresn" value="" > Need More Details on Products</button></a>';
			$vars = Array( 'discountamount'=> $discountamount,'entity_id' => $entityid ,'custfirstname' =>$namecust,'order' => $order,'orderno' =>$orderId,'email'=>$email,'name' => $productname,'grandtotal' => $grandtotal,'ebslinkurl' =>$ebslinkurl,'base_row_total_incl_tax' =>$subtotal,'sku' =>$sku, 'qty_ordered'=>$qty, 'action' =>$button,'newgrandtotal' => $newgrandTotal,
					);

			//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
		//			->sendTransactional($templateId1, $sender,  $email, $namecust, $vars, $storeId);
					$_email->sendTransactional($templateId1, $sender,  $email, $namecust, $vars, $storeId);
			
			
		//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
		//		->sendTransactional($templateId, $sender, "manoj@craftsvilla.com", $namecust, $vars, $storeId);
			 $_email->sendTransactional($templateId, $sender, "manoj@craftsvilla.com", $namecust, $vars, $storeId);
		
			//$translate->setTranslateInline(true);							
		}	

}
?>
