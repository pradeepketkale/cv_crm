<?php 
error_reporting(E_ALL & ~E_NOTICE);
$mageFilename = 'app/Mage.php';
require_once $mageFilename;

$readId = Mage::getSingleton('core/resource')->getConnection('core_read');


 $queryofholdshipment = "SELECT sfog.`increment_id` as orderId,sfog.`created_at` as ordered_date,sfog.`billing_name` as cust_name,sfoa.`telephone` as mobile,sfoa.`email` as email,sfog.`status` as status,sfog.`base_grand_total` as grandTotal FROM `sales_flat_order_grid` as sfog LEFT JOIN `sales_flat_order_address` as sfoa ON sfog.`entity_id` = sfoa.`parent_id` AND sfoa.`address_type` = 'billing' WHERE sfog.`status` IN ('holded','canceled') AND  (sfog.`created_at` > DATE_SUB(Now(),INTERVAL 10 DAY)) AND sfog.`base_grand_total` >= '10000.0000'";

$resultofshipmentquery = $readId->query($queryofholdshipment)->fetchAll();


$holdShipmentHtml .= "<table><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Order ID</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Ordered Date</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Customer</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Contact no.</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>EmailId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Status</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Grand Total</td></tr>";
	//while($_resultofshipmentquery = mysql_fetch_array($resultofshipmentquery))
	foreach($resultofshipmentquery as $_resultofshipmentquery)
	{
		$orderId = $_resultofshipmentquery['orderId'];
		$orderDate = $_resultofshipmentquery['ordered_date'];
		$cust_name = $_resultofshipmentquery['cust_name'];
		$mobile = $_resultofshipmentquery['mobile'];
		$email = $_resultofshipmentquery['email'];
		$status = $_resultofshipmentquery['status'];
		$grandTotal = $_resultofshipmentquery['grandTotal'];
		
		
		$holdShipmentHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$orderId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$orderDate."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$cust_name."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$mobile."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$email."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$status."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$grandTotal."</td></tr>";
		
	}
//echo $holdShipmentHtml;exit;

$message = "Seller Shipping Reminder Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Monica Gupta');
$mail->setToEmail('monica@craftsvilla.com');
$mail->setBody($holdShipmentHtml);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();



