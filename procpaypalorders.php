<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();



$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$orderDetail = "SELECT * FROM `sales_flat_order` WHERE  `created_at` > DATE_SUB(Now(),INTERVAL 2 DAY) AND `status` = 'fraud'";
$resultorderDetail = $db->query($orderDetail)->fetchAll();
$k = 0;
$numproc = 0;
$ordersnum = "";
echo "Total Paypal Orders:"; echo mysql_num_rows($resultorderDetail); 
//while($_resultorderDetail = mysql_fetch_array($resultorderDetail))
 foreach($resultorderDetail as $_resultorderDetail)
{
	if($k < 25)
	{
	$orderIds = $_resultorderDetail['entity_id'];
	$order = Mage::getModel('sales/order')->load($orderIds); //load order             
	//echo '<pre>';print_r($order);exit;
	$entityIdSus = $order->getEntityId();
	echo $incrementId = $order->getIncrementId(); 
	$custemail = $order->getCustomerEmail();
	$state = 'processing';
	$status = $state;
		

	$comment = "Status Changed to Processing by System Script";
	$isCustomerNotified = false; //whether customer to be notified
	if($entityIdSus != '')
	{
		$order->setState($state, $status, $comment, $isCustomerNotified);    
		$order->save();
		$order->sendNewOrderEmail();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("This Order No:- ".$incrementId." Status has changes to processing.."));
		echo $ordersnum .= "This Order No:- ".$incrementId." Status has changes to processing.."."\n";
		$numproc++; 
	}
	}
	$k++;
}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Total Paypal Orders:".$k.", Orders Converted:".$numproc." At Time ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($ordersnum);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();


