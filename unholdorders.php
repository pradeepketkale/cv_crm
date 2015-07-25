<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$orderDetail = "SELECT * FROM `sales_flat_order` WHERE  `created_at` BETWEEN DATE_SUB(Now(),INTERVAL 10 DAY) AND DATE_SUB(Now(),INTERVAL 2 HOUR) AND `status` = 'holded'";
$resultorderDetail =  $db->query($orderDetail)->fetchAll();
$k = 0;
$numproc = 0;
$numunhold = 0;
echo "Total Hold Orders:"; echo mysql_num_rows($resultorderDetail);

//while($_resultorderDetail = mysql_fetch_array($resultorderDetail)){
foreach($resultorderDetail as $_resultorderDetail){
if($k < 50)
{
	$orderId = $_resultorderDetail['increment_id'];
	echo "Working on Order:".$orderId;
	$parentId = $_resultorderDetail['entity_id'];
	$order = Mage::getModel('sales/order')->load($parentId);
	//echo '<pre>';print_r($order->getData());
		
	$comment = 'Order has been unholded by System';
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
		
			if ($myvar8 == 'Authorized')
				{
					if($myvar9 == 'NO')
					{
						if($order->canUnhold()) 
						{
							echo "Unholding Order";
							$order->unhold();
							$order->addStatusHistoryComment('Status has been unholded by  system');
							$order->save();
						}
						echo "Payment of this Order :-".$myvar4." is Authorised and Not Flagged";
					}
					else
					{
						echo "Payment of this Order :-".$myvar4." is Authorised and is Flagged";
					}	
						$numproc++;
				}
					
			else
			{
					if($order->canUnhold()) 
						{
							$numunhold++;
							echo "Unholding Order";
							$order->unhold();
							$order->addStatusHistoryComment('Status has been unholded by  system');
							$order->save();
						}
			}	
		
	}
	curl_close($ch);
}
$k++;
echo "Loop Done:".$k; 
echo "\n";
}
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Total Hold Orders:".$k.", Unholded: ".$numunhold.", Payment Received:".$numproc." At Time ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
