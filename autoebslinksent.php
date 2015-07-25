<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$order = Mage::getModel('sales/order')->getCollection();
		   $order->getSelect()
      			->join(array('a'=>'sales_flat_order_address'), 'a.parent_id=main_table.entity_id', array('telephone','street', 'city', 'region', 'postcode','country_id'))
				//->join(array('b'=>'ebslink'), 'main_table.increment_id=b.order_no', array('ebslink_id','order_no', 'ebslinkurl', 'comment', 'created_time'))
				->joinLeft('sales_flat_order_payment', 'main_table.entity_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.status = "pending" AND ((main_table.created_at < (NOW() - INTERVAL 10 MINUTE)) AND (main_table.created_at > (NOW() - INTERVAL 30 MINUTE))) AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","payucheckout_shared") AND a.address_type = "billing"');
				//->where('main_table.increment_id = "100016606"');
//echo "Query:".$order->getSelect()->__toString();exit();


$ebsorder = $order->getData();
foreach($ebsorder as $_ebsorder)
	{
		//sleep(1);
		echo "Loop Starting";
		$status = 'holded';		
		$comment = 'Ebslink sent to customer By System';
		$countHoldOrder = 0;
        $countNonHoldOrder = 0;
		
		$read = Mage::getSingleton('core/resource')->getConnection('ebslink_read');
		/*$_orderData = $read->query("SELECT * FROM `sales_flat_order` as sfo
									LEFT JOIN `sales_flat_order_address` as sfoa 
									ON sfo.`entity_id` = sfoa.`parent_id`
									LEFT JOIN `ebslink` as es
									ON sfo.`increment_id` = es.`order_no`
									WHERE sfo.`entity_id` = '".$_orderId."' and sfoa.`address_type` = 'billing'")->fetchAll();
				*/	/*echo '<pre>';
					print_r($_orderData);*/
					
					$namecust = $_ebsorder['customer_firstname'];
					echo $email = $_ebsorder['customer_email'];
					$currencyTotal = Mage::app()->getLocale()->currency($_ebsorder['order_currency_code'])->getSymbol();
					$currency = $_ebsorder['order_currency_code'];
					$grandtotal = $_ebsorder['grand_total'];
					//$ebslinkurl =  $_orderData[0]['ebslinkurl'];
					$entityid = $_ebsorder['entity_id'];
					echo $incrementid = $_ebsorder['increment_id'];
					$_customerTelephone = str_replace('/','',substr($_ebsorder['telephone'],0,10));
					$address1 = $_ebsorder['street'];
					$address = rip_tags($address1);
					$city = $_ebsorder['city'];
					$region = $_ebsorder['region'];
                                        $postcode1 = '000000'.$_orderData[0]['postcode'];
					$postcode = substr($postcode1,strlen($postcode1)-6,6);					
                                        $country_id = $_ebsorder['country_id'];
					$total_qt_ordered = $_ebsorder['total_qty_ordered'];
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
				/*$j = 0;
				while ($j < $totalItems) { 
					$fields_string .= 'products['.$j.'][name]'.'='.$products[$j]['name'].'&';
					$fields_string .= 'products['.$j.'][qty]'.'='.$products[$j]['qty'].'&';
					$fields_string .= 'products['.$j.'][price]'.'='.$products[$j]['price'].'&';
					$j++;
					}*/
				
				rtrim($fields_string, '&');
				
				//$url = 'https://secure.ebs.in/api/1_0';
				//$url = 'http://www.craftsvilla.com';
				
				//$myvars = 'Action=getCurrencyValue' . '&AccountID=' . $myvar2 . '&SecretKey='.$myvar3 . '&Currency='.$myvar4;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				
				
				$response = curl_exec( $ch );
				if(curl_errno($ch))
				{		
				echo "Curl Error Occured";
				//print curl_error($ch);
				}
				else
				{
				//print_r($response);exit;
				//echo $response;exit;
				
				$xml = @simplexml_load_string($response);
				/*echo '<pre>';
				print_r($xml);exit;*/
				$ebslinkurl = (string)$xml->invoice[0]->payment_url;
				$ebslinkinvoiceId = (string)$xml->invoice[0]->invoice_id;	
				//$ebslinkurl = 'http://craftsvilla.com';
				$readEbslink = $read->query("select * from `ebslink` WHERE `order_no` = '".$incrementid."'")->fetch();
				
				if($readEbslink) {
					echo 'Ebslink Email & SMS Already Sent! ';
					//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Email & SMS Already Sent! '));
					}
				else
					{
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$updateEbslink = $write->query("INSERT INTO `ebslink` (`order_no`, `ebslinkurl`,`created_time`,`order_id`,`ref_no`) VALUES ('".$incrementid."', '".$ebslinkurl."',NOW(),'".$entityid."','".$ebslinkinvoiceId."')");
					echo 'Ebslink Email & SMS Sent Successfully! ';
					$orderStatus = Mage::getModel('sales/order')->load($_ebsorder['entity_id']);
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
						//$this->_getSession()->addError($this->__('%s order(s) were not put on hold.', $countNonHoldOrder));
						}
					 else {
						echo '<pre>2'.'No order(s) were put on hold.';
						//$this->_getSession()->addError($this->__('No order(s) were put on hold.'));
						}
					}
				if ($countHoldOrder)
					 {
						echo '<pre>3'.'%s order(s) have been put on hold.', $countHoldOrder;
						//$this->_getSession()->addSuccess($this->__('%s order(s) have been put on hold.', $countHoldOrder));
					 }
				
				Mage::getModel('sales/order_status_history')
						->setParentId($_ebsorder['entity_id'])
						->setStatus($status)
						->setComment($comment)
						->setCreatedAt(NOW())
						->save();
						//$this->addStatusHistory($history);
				echo '<pre>4'.'save in order & status changed'.$_ebsorder['increment_id'];
							//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ebslink Email & SMS Sent Successfully! '));
						}
				}
				curl_close($ch);
				Mage::getModel('ebslink/ebslink')->sendebslink($incrementid); 	
				
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
