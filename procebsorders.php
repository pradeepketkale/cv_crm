<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$orderDetail = "SELECT * FROM `sales_flat_order` WHERE  `created_at` BETWEEN DATE_SUB(Now(),INTERVAL 12 DAY) AND DATE_SUB(Now(),INTERVAL 1 HOUR) AND `status` = 'pending'";
$resultorderDetail = $db->query($orderDetail)->fetchAll();
$k = 0;
$numproc = 0;
$numcan = 0;
echo "Total Orders:"; echo mysql_num_rows($resultorderDetail); 
$orderconsummary = "";
//while($_resultorderDetail = mysql_fetch_array($resultorderDetail)){
foreach($resultorderDetail as $_resultorderDetail){
if($k < 1000)
{
	$orderId = $_resultorderDetail['increment_id'];
	echo "Working on Order Id:"; echo $orderId; 
	$parentId = $_resultorderDetail['entity_id'];
	$order = Mage::getModel('sales/order')->load($parentId);
	//echo '<pre>';print_r($order->getData());
		
	$comment = 'Order has been canceled through System';
	$url = 'https://api.secure.ebs.in/api/1_0';
	$myvar1 = 'statusByRef';
	$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
	$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
	$myvar4 = $orderId;//'100217244';

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
	if(curl_errno($ch))
	{		
		print curl_error($ch);
	}
	else
	{
		$xml = @simplexml_load_string($response);
		//print_r($xml);
		$myvar8 = $xml['transactionType'];
		$myvar9 = $xml['isFlagged'];
		$comment = "Status has been changed through system to suspect fraud ";
			if ($myvar8 == 'Authorized')
				{
				$comment = "Status has been changed to processing by system script because Payment Received in EBS";
				echo $comment;
				//$order->setState('payment_review', 'fraud', $comment, '');    
				if($numproc < 25){
					$payment = $order->getPayment();
					$order->setState('processing', 'processing', $comment, '');    
					$payment->setMethod('purchaseorder');
					$payment->save();
					$order->save();	
					try {
						$order->sendNewOrderEmail();
					} catch (Exception $e)
					{
						echo "Unable to send Email";
					}
					$orderconsummary .= "Payment of this Order :-".$myvar4." is received in EBS and converted!!!    ";
				}
				$numproc++;

				//$order->setState('payment_review');
				//$order->setStatus('fraud');
				//$order->save();					
					//if($myvar9 == 'NO')
					//{
						
					//Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("Payment of this Order :-".$myvar4." is Authorised and Not Flagged"));
					//}
					//else
					//{
						//Mage::getSingleton('adminhtml/session')->addSucess(Mage::helper('adminhtml')->__("Payment of this Order :-".$myvar4." is Authorised and is Flagged"));
					//}	
				}
					
			else
			{
				//echo "Cancelling the order";
				//if($order->canCancel()) 
				//	{
				//	$order->cancel();
				//	$order->addStatusHistoryComment('Status has been changed to Cancel through system');
				//	$order->save();
				//	$numcan++;
				//	}
				//else { echo "Cannot Be Cancelled";}
			}	
		
	}
	curl_close($ch);
}
$k++;
echo "Loop Done:".$k;
echo "\n"; 
}
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "EBS Processing: Total  Orders:".$k.", Order Processed:".$numproc." At Time ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($orderconsummary);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();


