<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('entity_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder")')// a.udropship_status IN (1,15,17)
				->where('main_table.shipment_id = "100060245"');
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)
      	
/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();
*/      $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
		
	foreach($shipmentpayout_report1_arr as $row)
		{
		//print_r($row);exit;	
		//$orderID = $row['order_id'];
		$id = $row['entity_id'];
		$refundedAmount = $row['refundtodo'];
		echo $amtordId = $row['order_id'];
		$shipmentId = $row['shipment_id'];
		$payoutStatus = $row['shipmentpayout_status'];
		$payoutAdjust = $row['adjustment'];
		$total_amount1 = $row['subtotal'];
		$total_amount = $row['subtotal'];
		$vendorId = $row['udropship_vendor'];
		$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
		$closingbalance = $collectionVendor->getClosingBalance();
		$shipment = Mage::getModel('sales/order_shipment')->load($id);
		$vendors = Mage::helper('udropship')->getVendor($row['udropship_vendor']);
		$commentText = 'Refunded By system of amount of Rs-  '.$refundedAmount.'';
					
                
		 
		 		$url = 'https://secure.ebs.in/api/1_0';
				$myvar1 = 'statusByRef';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();//'7509';
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();//'694862a59816baf0c4b165baf4a6b3cf';exit;
				$myvar4 = $amtordId;
				$myvar5 = 'capture';
				$myvar10 = 'refund';
				$myvar11 = $refundedAmount;
				
				$fields = array(
							'Action' => urlencode($myvar1),
							'AccountID' => urlencode($myvar2),
							'SecretKey' => urlencode($myvar3),
							'RefNo' => urlencode($myvar4)
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
	
				$response = curl_exec( $ch );
				//var_dump($response);exit;
				if(curl_errno($ch))
				{		
					print curl_error($ch);
				}
				else
				{
					$xml = @simplexml_load_string($response);
					$myvar6 = $xml['amount'];
					$myvar7 = $xml['paymentId'];
					$myvar8 = $xml['transactionType'];
					$myvar9 = $xml['isFlagged'];
	
					if($myvar8 == 'Authorized')
					{
							if($myvar9 == 'NO')
								{
									echo "The Payment For this Order is Not flagged :-".$myvar4;
									//Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is Not flagged :-".$myvar4));
								}
							else
								{
									echo "The Payment For this Order is Flagged :-".$myvar4;
									//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Flagged :-".$myvar4));
								}
					}
					else
					{
					echo "This Order Payment is not Authorized :-".$myvar4;
					//Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Order Payment is not Authorized :-".$myvar4));
					}
				}
				curl_close($ch);
	

				if($myvar8 == 'Authorized')
					{
				
					$fields2 = array(
						'Action' => urlencode($myvar10),
						'AccountID' => urlencode($myvar2),
						'SecretKey' => urlencode($myvar3),
						'Amount' => urlencode($myvar11),
						'PaymentID' =>urlencode($myvar7)
					);
					$fields_string2 = '';
					//url-ify the data for the POST
					foreach($fields2 as $key=>$value) { $fields_string2 .= $key.'='.$value.'&'; }
					rtrim($fields_string2, '&');
					$ch2 = curl_init();
					curl_setopt($ch2, CURLOPT_VERBOSE, 1); 
					curl_setopt($ch2,CURLOPT_URL, $url);
					curl_setopt($ch2,CURLOPT_POSTFIELDS, $fields_string2);
					curl_setopt( $ch2, CURLOPT_POST, 1);
					curl_setopt( $ch2, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt( $ch2, CURLOPT_HEADER, 0);
					curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
					$response2 = curl_exec( $ch2 );
					if(curl_errno($ch2))
						{		
						print curl_error($ch2);
						}
					else
						{
						$xml2 = @simplexml_load_string($response2);
					
					/*echo '<pre>';
					print_r($xml2);*/
							if($xml2['errorCode'] == '24')
							{
								Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('This payment is not captured'));
							}
							else
							{
								$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
								$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$myvar11."' WHERE `order_id` = '".$myvar4."'";
								$write->query($queryRefamt);
								echo "The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11;
								//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11));			
						//query for change the status of refund intiated		
								$writeshipment = Mage::getSingleton('core/resource')->getConnection('core_write');	
								$queryShipmentUpdate = "update `sales_flat_shipment` set `udropship_status` = '12' WHERE `increment_id` = '".$shipmentId."'";
								$writeshipment->query($queryShipmentUpdate);
								echo "The shipmentno.".$shipmentId." status has changes to refund initiated";			
								$comment = Mage::getModel('sales/order_shipment_comment')
                  	  			->setParentId($id)
								->setComment($commentText)
                    			->setCreatedAt(NOW())
								->setUdropshipStatus(@$statuses[12])
								->save();
                				$shipment->addComment($comment);		
			
			//for adjustment copied from shipment controller.php
								
			
			if($payoutStatus == 1)
				{
					$order = Mage::getModel('sales/order')->loadByIncrementId($row['order_id']);
					$_orderCurrencyCode = $order->getOrderCurrencyCode();
					if($_orderCurrencyCode != 'INR') 
					$total_amount = $row['subtotal']/1.5.'<br>';
					$commission_amount = $row['commission_percent'].'<br>';
					$itemised_total_shippingcost = $row['itemised_total_shippingcost'].'<br>';
					$base_shipping_amount = $row['base_shipping_amount'];
				
							if($vendors->getManageShipping() == "imanage")
							{
			
								$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
								$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$row['cod_fee'])*0.97 - $vendor_amount;
							}
							else
							   {
								$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
								$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
								}

						
					$payoutAdjust = $payoutAdjust-$vendor_amount;
					
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipmentId."'";
					$write->query($queryUpdate);
					
					$closingbalance = $closingbalance - $vendor_amount;
					$queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
					$write->query($queyVendor);
					
					/*$comment = Mage::getModel('sales/order_shipment_comment')
                  	  			->setParentId($id)
								->setComment($commentText)
                    			->setCreatedAt(NOW())
								->setUdropshipStatus(@$statuses[12])
								->save();
                	$shipment->addComment($comment);*/
				//echo '<pre>';
				//print_r($comment->getData());
				//$shipment->addComment($comment);
				//print_r($shipment->getData());exit;
				//print_r($shipment);
				
				}
				else
					{
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipmentId."'";
					$write->query($queryUpdate);
						
					}
		
				}
			}
			curl_close($ch2);
			
				}
			else
				{
				echo "The Payment For this Order is not ready to Refunded :-".$myvar4;
				//Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is not ready to Refunded :-".$myvar4));
				}
			
	
}
