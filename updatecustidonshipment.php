<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$bodymessage = '';

$shipmentDetails = "SELECT * FROM `sales_flat_shipment` WHERE `customer_id` IS NULL AND `created_at` between  (DATE_SUB(NOW(),INTERVAL 1 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR))";
$shipmentResult = $readcon->query($shipmentDetails)->fetchAll();
$k=0;
$numdisable = 0;
foreach($shipmentResult as $_shipmentResult)
{
	if($k < 2)
	{		
	$orderIDD = $_shipmentResult['order_id'];
	$incrementId = $_shipmentResult['increment_id'];
	$getlastcustid = $_shipmentResult['customer_id'];
	$getOrderData = Mage::getModel('sales/order')->load($orderIDD);
	$getCustEmail = $getOrderData->getCustomerEmail();
	$customer = Mage::getModel('customer/customer');
	$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
	$customer->loadByEmail($getCustEmail);
	$getIdcustomer = $customer->getId();
	if($getlastcustid == '')
	{
		$updatecustid = "UPDATE `sales_flat_shipment` SET `customer_id`='".$getIdcustomer."' WHERE `order_id` = '".$orderIDD."'";
		$write->query($updatecustid);
		$bodymessage .= "updated the shipment ".$incrementId." and custid is ".$getIdcustomer."<br>";
	//exit;
	}
	$numdisable++;		
	}
$k++;
}

	$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
	$subject = "Total Guest Shipment: ".$k.", Shipment Assigned to Customer:".$numdisable." At Time ".$currentTime;

	$mail = Mage::getModel('core/email');
	$mail->setToName("Manoj");
	$mail->setToEmail("manoj@craftsvilla.com");
	$mail->setBody($bodymessage);
	$mail->setSubject($subject);
	$mail->setFromEmail('dileswar@craftsvilla.com');
	$mail->setFromName("Dileswar");
	$mail->setType('html');
	$mail->send();

