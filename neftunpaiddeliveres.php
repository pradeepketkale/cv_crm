<?php

require_once 'app/Mage.php';
Mage::app();

$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');

$merchantIds[] = array("Vendor Id", "Merchant Id");

$merchantQuery = "SELECT * FROM `udropship_vendor`";
$merchantQueryRes = $readcon->query($merchantQuery)->fetchAll();
$basePath = Mage::getBaseDir();
$csvPath  = $basePath.DS.'media'.DS.'vendorcsv'.DS;
			$fileNameMerchant = 'merchantIds.csv';
			$filepathMerchant = $csvPath.$fileNameMerchant;
			$fpNeft = fopen($filepathMerchant, 'w');
				
foreach($merchantQueryRes as $_merchantQueryRes) {
	$merchantIds[] = array($_merchantQueryRes['vendor_id'], $_merchantQueryRes['merchant_id_city']);
}
				
		   	foreach ($merchantIds as $fields) {
				fputcsv($fpNeft, $fields);
			}
			fclose($fpNeft);
exit;

$unpaidDeliverAramex[] = array("Shipment Id", "Updated Date", "Amount", "TrackingNumber");
$unpaidDeliverFedex[] = array("Shipment Id", "Updated Date", "Amount", "TrackingNumber");

$unpaidDeliverAramexQuery = "SELECT sp.shipment_id,sfs.udropship_vendor,sfs.base_total_value,sfs.base_shipping_amount, sfs.created_at,sfs.entity_id, sfs.updated_at FROM shipmentpayout sp LEFT JOIN sales_flat_shipment sfs ON sp.shipment_id = sfs.increment_id where sp.shipmentpayout_status = 0 AND sfs.udropship_status = 7 AND sfs.updated_at < (DATE_SUB(NOW(),INTERVAL 30 DAY))";
$unpaidDeliverAramexRes = $readcon->query($unpaidDeliverAramexQuery)->fetchAll();
//print_r($unpaidDeliverAramexRes); exit;

$fileNameUnpaidAramex = 'unpaid_delivered_aramex.csv';
$filepathAra = $csvPath.$fileNameUnpaidAramex;
$fpAra = fopen($filepathAra, 'w');
$fileNameFed = 'unpaid_delivered_fedex.csv';
$filepathFed = $csvPath.$fileNameFed;
$fpFed = fopen($filepathFed, 'w');
		
foreach($unpaidDeliverAramexRes as $_unpaidDeliverAramexRes) 
{
	$shipmentid = $_unpaidDeliverAramexRes["shipment_id"];
	$entityid = $_unpaidDeliverAramexRes["entity_id"];
	$updatedat = $_unpaidDeliverAramexRes["updated_at"];
	$amount = $_unpaidDeliverAramexRes["base_total_value"]+$_unpaidDeliverAramexRes["base_shipping_amount"];
	$trackdetails = "SELECT * from sales_flat_shipment_track where parent_id = '".$entityid."' AND courier_name = 'Aramex'";
	$trackdetailsRes = $readcon->query($trackdetails)->fetchAll();

	foreach($trackdetailsRes as $_trackdetailsRes) 
	{
		$trackNumber = $_trackdetailsRes['number'];
		$courier = $_trackdetailsRes['courier_name'];
		$unpaidDeliverAramex[] = array($shipmentid, $updatedat, $amount, $trackNumber);
	}

	$trackdetailsFedex = "SELECT * from sales_flat_shipment_track where parent_id = '".$entityid."' AND courier_name = 'Fedex'";
	$trackdetailsFedexRes = $readcon->query($trackdetailsFedex)->fetchAll();

	foreach($trackdetailsFedexRes as $_trackdetailsFedexRes) 
	{
		$trackNumberFedex = $_trackdetailsFedexRes['number'];
		$courierFedex = $_trackdetailsFedexRes['courier_name'];
		$unpaidDeliverFedex[] = array($shipmentid, $updatedat, $amount, $trackNumberFedex);
	}

}

	foreach ($unpaidDeliverAramex as $fields) 
	{
			fputcsv($fpAra, $fields);
	}
			fclose($fpAra);
			
	foreach ($unpaidDeliverFedex as $fields) 
	{
			fputcsv($fpFed, $fields);
	}
			fclose($fpFed);


$shippedToCusAramex[] = array("Shipment Id", "Updated Date", "Amount", "TrackingNumber");
$shippedToCusFedex[] = array("Shipment Id", "Updated Date", "Amount", "TrackingNumber");

$shippedToCusQuery = "SELECT sp.shipment_id,sp.payment_amount,sfs.udropship_vendor,sfs.base_total_value, sfs.base_shipping_amount, sfs.created_at,sfs.entity_id, sfs.updated_at FROM shipmentpayout sp LEFT JOIN sales_flat_shipment sfs ON sp.shipment_id = sfs.increment_id where sp.shipmentpayout_status = 0 AND sfs.udropship_status = 1 AND sfs.updated_at < (DATE_SUB(NOW(),INTERVAL 30 DAY))";
$shippedToCusQueryRes = $readcon->query($shippedToCusQuery)->fetchAll();
//print_r($shippedToCusQueryRes); exit;

$fileNameShippedCustAramex = 'unpaid_shipped_to_customer_aramex.csv';
$filepathShippedCusAra = $csvPath.$fileNameShippedCustAramex;
$fpShippedAra = fopen($filepathShippedCusAra, 'w');
$fileNameShippedFedex = 'unpaid_shipped_to_customer_fedex.csv';
$filepathShippedCusFed = $csvPath.$fileNameShippedFedex;
$fpShippedFed = fopen($filepathShippedCusFed, 'w');
			
foreach($shippedToCusQueryRes as $_shippedToCusQueryRes) 
{
	$shipmentid = $_shippedToCusQueryRes["shipment_id"];
	$entityid = $_shippedToCusQueryRes["entity_id"];
	$updatedat = $_shippedToCusQueryRes["updated_at"];
	$amount = $_shippedToCusQueryRes["base_total_value"]+$_shippedToCusQueryRes["base_shipping_amount"];

	$trackdetailsAramex = "SELECT * from sales_flat_shipment_track where parent_id = '".$entityid."' AND courier_name = 'Aramex'";
	$trackdetailsAramexRes = $readcon->query($trackdetailsAramex)->fetchAll();
	foreach($trackdetailsAramexRes as $_trackdetailsAramexRes) 
	{
		$trackNumber = $_trackdetailsAramexRes['number'];
		$courier = $_trackdetailsAramexRes['courier_name'];
		$shippedToCusAramex[] = array($shipmentid, $updatedat, $amount, $trackNumber);
	}
				
$trackdetailsShippedCusFedex = "SELECT * from sales_flat_shipment_track where parent_id = '".$entityid."' AND courier_name = 'Fedex'";
$trackdetailsShippedCusFedexRes = $readcon->query($trackdetailsShippedCusFedex)->fetchAll();
	
	foreach($trackdetailsShippedCusFedexRes as $_trackdetailsShippedCusFedexRes) 
	{
		$trackNumberFedex = $_trackdetailsShippedCusFedexRes['number'];
		$courierFedex = $_trackdetailsShippedCusFedexRes['courier_name'];
		$shippedToCusFedex[] = array($shipmentid, $updatedat, $amount, $trackNumberFedex);
	}

}

				foreach ($shippedToCusAramex as $fields) {
					fputcsv($fpShippedAra, $fields);
				}
				fclose($fpShippedAra);


			   	foreach ($shippedToCusFedex as $fields) {
					fputcsv($fpShippedFed, $fields);
				}
				fclose($fpShippedFed);

			
				
				
					$message = "http://local.craftsvilla.com/media/vendorcsv/merchantIds.csv".'<br>';
					
					$body = '<a href="http://local.craftsvilla.com/media/vendorcsv/merchantIds.csv">click here to download the Merchant IDS</a>'.'<br>';
					$body .= '<a href="http://local.craftsvilla.com/media/vendorcsv/unpaid_delivered_aramex.csv">click here to download the Unpaid Delivered Aramex</a>'.'<br>';
					$body .= '<a href="http://local.craftsvilla.com/media/vendorcsv/unpaid_delivered_fedex.csv">click here to download the Unpaid Delivered Fedex</a>'.'<br>';
					$body .= '<a href="http://local.craftsvilla.com/media/vendorcsv/unpaid_shipped_to_customer_aramex.csv">click here to download the Unpaid Shipped To Customer Aramex</a>'.'<br>'; 
					$body .= '<a href="http://local.craftsvilla.com/media/vendorcsv/unpaid_shipped_to_customer_fedex.csv">click here to download the Unpaid Shipped To Customer Fedex</a>';
					
					$mail = Mage::getModel('core/email');
					$mail->setToName('Craftsvilla Places');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($body);
					$mail->setSubject($message);
					$mail->setFromEmail('srilatharapolu444@gmail.com');
					$mail->setFromName("Tech");
					$mail->setType('html');
	
					if($mail->send())
					{echo "Email sent to your emailid sucessfully";}
					else {echo "Failed to send Email";}
	
echo "<br>";

?> 
