<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');


$orderDetail = "SELECT * FROM `sales_flat_order` WHERE  `created_at` > DATE_SUB(Now(),INTERVAL 2 MONTH) AND `status` = 'fraud'";
$resultorderDetail = $db->query($orderDetail)->fetchAll();

//while($_resultorderDetail = mysql_fetch_array($resultorderDetail))
foreach($resultorderDetail as $_resultorderDetail)
{

	$orderIds = $_resultorderDetail['entity_id'];
	$order = Mage::getModel('sales/order')->load($orderIds); //load order             
	//echo '<pre>';print_r($order);exit;
	$entityIdSus = $order->getEntityId();
	$incrementId = $order->getIncrementId();
	$custemail = $order->getCustomerEmail();
	$state = 'processing';
	$status = $state;
		

	$comment = "AWRP, status changes to  to $status Status ";
	$isCustomerNotified = false; //whether customer to be notified
	if($entityIdSus != '')
	{
		$order->setState($state, $status, $comment, $isCustomerNotified);    
		$order->save();
		$order->sendNewOrderEmail();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("This Order No:- ".$incrementId." Status has changes to processing.."));
		echo "This Order No:- ".$incrementId." Status has changes to processing.."."\n";
	}
}


