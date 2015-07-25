<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "EBS Capture Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

$conn = mysql_connect("newserver4.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
mysql_select_db("nzkrqvrxme");
//$sql = "SELECT * FROM `sales_flat_order` WHERE `created_at` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND DATE_SUB(CURDATE(), INTERVAL 0 DAY)";
$sql = "SELECT * FROM `sales_flat_order` WHERE `status` IN('processing','closed','complete') AND `created_at` BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(NOW(), INTERVAL 8 DAY)";
$results = mysql_query($sql);
$row = mysql_fetch_array($results);
while ($row = mysql_fetch_array($results)) {
		//$row['increment_id'].'<br>';
		$url = 'https://secure.ebs.in/api/1_0';
		$myvar1 = 'statusByRef';
		$myvar2 = '7509';
		$myvar3 = '694862a59816baf0c4b165baf4a6b3cf';
		$myvar4 = $row['increment_id'];
		$myvar5 = 'capture';

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
		$myvar6 = $xml['amount'];
		$myvar7 = $xml['paymentId'];
		$myvar8 = $xml['transactionType'];
		$myvar9 = $xml['isFlagged'];

	if($myvar8 == 'Authorized')
				{
						if($myvar9 == 'NO')
										{		
										$fields1 = array(
													'Action' => urlencode($myvar5),
													'AccountID' => urlencode($myvar2),
													'SecretKey' => urlencode($myvar3),
													'Amount' => urlencode($myvar6),
													'PaymentID' =>urlencode($myvar7)
												);
										$fields_string1 = '';
										//url-ify the data for the POST
										foreach($fields1 as $key=>$value) { $fields_string1 .= $key.'='.$value.'&'; }
										rtrim($fields_string1, '&');
										
										
										$ch1 = curl_init();
										curl_setopt($ch1, CURLOPT_VERBOSE, 1); 
										curl_setopt($ch1,CURLOPT_URL, $url);
										curl_setopt($ch1,CURLOPT_POSTFIELDS, $fields_string1);
										curl_setopt( $ch1, CURLOPT_POST, 1);
										curl_setopt( $ch1, CURLOPT_FOLLOWLOCATION, 1);
										curl_setopt( $ch1, CURLOPT_HEADER, 0);
										curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
										curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
										
										
										
										$response1 = curl_exec( $ch1 );
										if(curl_errno($ch1))
											{		
												print curl_error($ch1);
											}
										else
											{
										 $xml1 = @simplexml_load_string($response1);
										
										echo '<pre>';
										print_r($xml1);
											}
										curl_close($ch1);
									echo "The Payment For this Order is Captured :-".$myvar4;	
										}
									
								else{
									echo "The Payment For this Order is Flagged :-".$myvar4;
									}
					}
			else
				{
				echo "This Order Payment is not Authorized :-".$myvar4;
				}
	}
curl_close($ch);
}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "EBS Capture Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>
