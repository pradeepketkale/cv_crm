<?php
//require_once ('/home/amit/doejofinal/payu.php');
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
require_once(Mage::getBaseDir('lib').'/payu/payu.php');

$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('entity_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('a.udropship_status = 23 AND sales_flat_order_payment.method IN ("payucheckout_shared") AND main_table.refundtodo IS NOT NULL ')
				->order('a.increment_id','DESC');
      	
      //echo "Query:".$shipmentpayout_report1->getSelect()->__toString();exit;
		$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();			
	      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
	echo "Total to be refunded:".$shipmentpayout_report1->count(); 
$k = 0;
$bodymessage = '';
	foreach($shipmentpayout_report1_arr as $row)
	{
			if(($row['refundtodo'] != "") && ($row['refundtodo'] < 10000))
			{
				if($k < 150)
				{
					$id = $row['entity_id'];
					echo $refundedAmount = round($row['refundtodo'],0)-1;
					
					echo $orderid = $row['order_id']; 
					echo $shipmentId = $row['shipment_id']; 
					$payoutStatus = $row['shipmentpayout_status'];
					$payoutAdjust = $row['adjustment'];
					$total_amount1 = $row['subtotal'];
					$total_amount = $row['subtotal'];
					echo $vendorId = $row['udropship_vendor'];
					$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
					$closingbalance = $collectionVendor->getClosingBalance();
					$shipment = Mage::getModel('sales/order_shipment')->load($id);
					$vendors = Mage::helper('udropship')->getVendor($row['udropship_vendor']);
					$commentText = 'Refunded By system of amount '.$refundedAmount.'';
					
					echo "Getting MIHPayUID---";
					 echo $mihpayuid = getmihpayuid($orderid);
					//$key = "C0Dr8m";
					//$salt = "3sf0jURk";

					//$key = "gtKFFx";
					//$salt = "eCwWELxi";	

					$key = "ZwzD3B";//live
					$salt = "8HLsQdw4";//live
					$command = "cancel_refund_transaction";
					

					$hash_str = $key  . '|' . $command . '|' . $mihpayuid . '|' . $salt ;
					$hash = strtolower(hash('sha512', $hash_str));
					$tokenId = $shipmentId;
					$r = array('key' => $key , 'hash' =>$hash , 'var1' => $mihpayuid , 'command' => $command, 'var2' => $tokenId, 'var3' => $refundedAmount);
					
					$qs= http_build_query($r);

//					print_r($qs);exit;
					$wsUrl = "https://info.payu.in/merchant/postservice.php"; //live 
					//$wsUrl = "https://test.payu.in/merchant/postservice.php"; // local 
					$c = curl_init();
					curl_setopt($c, CURLOPT_URL, $wsUrl);
					curl_setopt($c, CURLOPT_POST, 1);
					curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
					curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
					$o = curl_exec($c);
					//print_r($o);exit;

			
						if (curl_errno($c)) {
						echo "Throwing an exception while refunding";
						$sad = curl_error($c);
						throw new Exception($sad);
						}
					   $valueSerialized = @unserialize($o);
						if($o === 'b:0;' || $valueSerialized !== false) {
						print_r($valueSerialized);
						}
					echo $refundStatus = $valueSerialized['status'];
					echo $refundMsg = $valueSerialized['msg']; 
					echo "--------------";
					if($refundStatus == 1)
					{
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$refundedAmount."' WHERE `order_id` = '".$orderid."'";
					$write->query($queryRefamt);
					$write->closeConnection();	
					$bodymessage .= "The Payment For this Shipment is Refunded :-".$shipmentId.", Amount".$refundedAmount."<br>";
					//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$orderid."Amount".$refundedAmount));			
										
			//query for change the status of refund intiated		
					$writeshipment = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$queryShipmentUpdate = "update `sales_flat_shipment` set `udropship_status` = '12' WHERE `increment_id` = '".$shipmentId."'";
					$writeshipment->query($queryShipmentUpdate);
					echo "The shipmentno.".$shipmentId." status changed to refund initiated";
					//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The shipmentno '.$shipmentId.' status has changes to refund initiated'));			
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
						$total_amount = $row['subtotal']/1.5;
						$commission_amount = $row['commission_percent'];
						$itemised_total_shippingcost = $row['itemised_total_shippingcost'];
						$base_shipping_amount = $row['base_shipping_amount'];
						
						$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1400)));
						
						$payoutAdjust = $payoutAdjust-$vendor_amount;
						
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
						
						$closingbalance = $closingbalance - $vendor_amount;
						$queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
						$write->query($queyVendor);
						
					}
					else
					{
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
					}
					}
					else
					{
						$bodymessage .= "The Payment For this Shipment is Not Refunded :-".$shipmentId.", Amount".$myvar11." Error Message: ".$refundMsg."<br>";
					}	
					curl_close($c);
					}
					else {break;}
					echo "-<><><><><><";
					echo $k++;	
					echo "-<><><><><><";
				}
				else
				{
					echo "The Payment For this Order is not ready to Refunded :-".$orderid;
					$bodymessage .= "The Payment For this Order is not ready to Refunded as refunded to do amount is missing or refund amount > 10k :-".$row['shipment_id']."<br>";
					
				}		
		
	}

$summary = "Total Payu Auto Refunded:".$k;

 $mail = Mage::getModel('core/email');
                                $mail->setToName('Manoj');
                                $mail->setToEmail('manoj@craftsvilla.com');
                                $mail->setBody($bodymessage);
                                $mail->setSubject($summary);
                                $mail->setFromEmail('places@craftsvilla.com');
                                $mail->setFromName("Craftsvilla Places");
                                $mail->setType('html');

                                try{
                                        if($mail->send())
                                        {
                                                echo 'Summary Email has been send successfully';
                                                $mail->setToEmail('monica@craftsvilla.com');
                                                $mail->send();
                                                echo 'Summary Email to Monica has been send successfully';
                                        }
                                } catch (Exception $e) {
                                        echo $e->getMessage();
                                }


function getmihpayuid($transactionid)
{
	//$key = "gtKFFx";
	//$salt = "eCwWELxi";
	$key = "ZwzD3B";//live
	$salt = "8HLsQdw4";//live

	$command = "verify_payment";
	$hash_str = $key  . '|' . $command . '|' . $transactionid . '|' . $salt ;
	$hash = strtolower(hash('sha512', $hash_str));
	// $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command, 'var2' => $salt, 'var3' => $amount);
	$r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command);

	$qs= http_build_query($r);
	//$wsUrl = "https://test.payu.in/merchant/postservice.php";
	$wsUrl = "https://info.payu.in/merchant/postservice.php";
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $wsUrl);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	$o = curl_exec($c);
	if (curl_errno($c)) {
	$sad = curl_error($c);
	throw new Exception($sad);
	}
	curl_close($c);

	$valueSerialized = @unserialize($o);
	if($o === 'b:0;' || $valueSerialized !== false) {
	echo '<pre>'; print_r($valueSerialized);
		if($valueSerialized['transaction_details'][$transactionid]['status'] == 'failure')
			return getmihpayuidalt($transactionid);
		else
			return $valueSerialized['transaction_details'][$transactionid]['mihpayid'];
	}
	return NULL;
			
}

function getmihpayuidalt($transactionid)
{
        //$key = "gtKFFx";
        //$salt = "eCwWELxi";
        $key = "ZwzD3B";//live
        $salt = "8HLsQdw4";//live
	$transactionid = $transactionid."P";
	echo "----------Entered Alt-------------".$transactionid;
        $command = "verify_payment";
        $hash_str = $key  . '|' . $command . '|' . $transactionid . '|' . $salt ;
        $hash = strtolower(hash('sha512', $hash_str));
        // $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command, 'var2' => $salt, 'var3' => $amount);
        $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command);

        $qs= http_build_query($r);
        //$wsUrl = "https://test.payu.in/merchant/postservice.php";
        $wsUrl = "https://info.payu.in/merchant/postservice.php";
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $wsUrl);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        $o = curl_exec($c);
        if (curl_errno($c)) {
	echo "Throwing an exception alt";
        $sad = curl_error($c);
        throw new Exception($sad);
        }
        curl_close($c);

        $valueSerialized = @unserialize($o);
        if($o === 'b:0;' || $valueSerialized !== false) {
        return $valueSerialized['transaction_details'][$transactionid]['mihpayid'];
        }
        return NULL;

}
?>

