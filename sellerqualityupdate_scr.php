<?php
require_once 'app/Mage.php';
Mage::app();

$readconnMain1 = Mage::getSingleton('core/resource')->getConnection('core_read');
$vendorInfo = "SELECT `vendor_id`,`vendor_name` FROM `vendor_info_craftsvilla` ORDER BY `vendor_id` DESC";
//$vendorInfo = "SELECT `vendor_id`,`vendor_name` FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '8489' ORDER BY `vendor_id` DESC";
$vendorInfoRes  = $readconnMain1->query($vendorInfo)->fetchAll();
//echo '<pre>'; print_r($vendorInfoRes); exit;
$readconnMain1->closeConnection();
$k = 0;
foreach($vendorInfoRes as $_vendorInfoRes) {
if ($k < 20000)
{
$readconnMain = Mage::getSingleton('core/resource')->getConnection('core_read');
$vendorId = $_vendorInfoRes['vendor_id'];
echo $vendorName = mysql_escape_string($_vendorInfoRes['vendor_name']); 

$shipments90Query = "SELECT COUNT(*) AS shipments90day FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendorId."' AND `created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY)";  
			
			$shipments90QueryRes = $readconnMain->query($shipments90Query)->fetch();
				//echo '<pre>';print_r($shipments90QueryRes); exit;
			$shipments90day = $shipments90QueryRes['shipments90day']; 
			
			$shipments30Query = "SELECT COUNT(*) AS shipments30day FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendorId."' AND `created_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)";
			
			$shipments30QueryRes = $readconnMain->query($shipments30Query)->fetch();
				//echo '<pre>';print_r($shipments30QueryRes); exit;
			$shipments30day = $shipments30QueryRes['shipments30day']; 
			
			
			 
			/*$shipmentsCOD90day = "SELECT COUNT(sfs.`entity_id`) AS CODcount,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY)";
			$shipmentsCOD90dayRes = $readconnMain->query($shipmentsCOD90day)->fetch();
			//print_r($shipmentsCOD90dayRes); exit;
			$codShipments90Day = $shipmentsCOD90dayRes['CODcount'];*/
			
			$refundratio90Query = "SELECT COUNT(*) AS refundratio90day FROM `sales_flat_shipment` WHERE `udropship_status`=12 AND `udropship_vendor` ='".$vendorId."'  AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)"; 
			$refundratio90QueryRes = $readconnMain->query($refundratio90Query)->fetch();
				//echo '<pre>';print_r($refundratio90QueryRes); exit;
			$refundratio90day = round(($refundratio90QueryRes['refundratio90day']/$shipments90day)*100,2);	
			
			
			
			
			$refundratio30Query = "SELECT COUNT(*) AS refundratio30day FROM `sales_flat_shipment` WHERE `udropship_status`=12 AND `udropship_vendor` ='".$vendorId."'  AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)"; 
			$refundratio30QueryRes = $readconnMain->query($refundratio30Query)->fetch();
				//echo '<pre>';print_r($refundratio30QueryRes); exit;
			$refundratio30day = round(($refundratio30QueryRes['refundratio30day']/$shipments30day)*100,2);
			
			
			
			$disputeratio90Query = "SELECT COUNT(*) AS disputeratio90day FROM `sales_flat_shipment` WHERE `udropship_status`=20 AND `udropship_vendor` ='".$vendorId."'  AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)"; 
			$disputeratio90QueryRes = $readconnMain->query($disputeratio90Query)->fetch();
				//echo '<pre>';print_r($disputeratio90QueryRes); exit;
			$disputeratio90day = round(($disputeratio90QueryRes['disputeratio90day']/$shipments90day)*100,2);
			
			
			$disputeratio30Query = "SELECT COUNT(*) AS disputeratio30day FROM `sales_flat_shipment` WHERE `udropship_status`=20 AND `udropship_vendor` ='".$vendorId."'  AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)"; 
			$disputeratio30QueryRes = $readconnMain->query($disputeratio30Query)->fetch();
				//echo '<pre>';print_r($disputeratio30QueryRes); exit;
			$disputeratio30day = round(($disputeratio30QueryRes['disputeratio30day']/$shipments30day)*100,2);
			
			
			$diff = 0;
			$diffnum = 0;
		    $dispatchprepaid90Query = "SELECT s.created_at as createdat, s.updated_at as updatedat, p.method as paymethod, TIMESTAMPDIFF(DAY,s.created_at,s.updated_at) AS diff FROM `sales_flat_shipment` as s LEFT JOIN `sales_flat_order_payment` as p ON s.order_id = p.parent_id where `udropship_vendor`='".$vendorId."' AND p.method != 'cashondelivery' AND s.udropship_status = '1'  AND s.created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)"; 
			$dispatchprepaid90QueryRes = $readconnMain->query($dispatchprepaid90Query)->fetchAll();
				//echo '<pre>';print_r($dispatchprepaid90QueryRes); exit;

			foreach($dispatchprepaid90QueryRes as $_dispatchprepaid90QueryRes) {
				$dispatchTime = $_dispatchprepaid90QueryRes['diff']; 		 
				$diff += $dispatchTime;
				$diffnum++;
			
			}
			$dispatchavg = round($diff/$diffnum,2);
			
			$diff30day = 0;
			$diffnum30day = 0;
			$dispatchprepaid30Query = "SELECT s.created_at as createdat, s.updated_at as updatedat, p.method as paymethod, TIMESTAMPDIFF(DAY,s.created_at,s.updated_at) AS diff FROM `sales_flat_shipment` as s LEFT JOIN `sales_flat_order_payment` as p ON s.order_id = p.parent_id where `udropship_vendor`='".$vendorId."' AND p.method != 'cashondelivery' AND s.udropship_status = '1'  AND s.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
			$dispatchprepaid30QueryRes = $readconnMain->query($dispatchprepaid30Query)->fetchAll();
				//echo '<pre>';print_r($dispatchprepaid30QueryRes); exit;
			foreach($dispatchprepaid30QueryRes as $_dispatchprepaid30QueryRes) {
				$dispatchTime30 = $_dispatchprepaid30QueryRes['diff']; 		 
				$diff30day += $dispatchTime30;
				$diffnum30day++;
			
			}
			$dispatchavg30day = round($diff30day/$diffnum30day,2);
			
			
		
			$codreturn90days = "SELECT COUNT(sfs.`entity_id`) AS codreturn90day,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY) AND sfs.`udropship_status` = 25";
			$codreturn90daysRes = $readconnMain->query($codreturn90days)->fetch();
				//echo '<pre>';print_r($codreturn90daysRes); exit;
			$codreturn90day = $codreturn90daysRes['codreturn90day'];
		
			
			$codreturn30days = "SELECT COUNT(sfs.`entity_id`) AS codreturn30day,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 30 DAY) AND sfs.`udropship_status` = 25";
			$codreturn30daysRes = $readconnMain->query($codreturn30days)->fetch();
				//echo '<pre>';print_r($codreturn30daysRes); exit;
			$codreturn30day = $codreturn30daysRes['codreturn30day'];
			
			
			$codcancel90days = "SELECT COUNT(sfs.`entity_id`) AS codcancel90day,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY) AND sfs.`udropship_status` = 6";
			$codcancel90daysRes = $readconnMain->query($codcancel90days)->fetch();
				//echo '<pre>';print_r($codcancel90daysRes); exit;
			$codcancel90day = $codcancel90daysRes['codcancel90day'];
			
			
			$codcancel30days = "SELECT COUNT(sfs.`entity_id`) AS codcancel30day,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 30 DAY) AND sfs.`udropship_status` = 6";
			$codcancel30daysRes = $readconnMain->query($codcancel30days)->fetch();
				//echo '<pre>';print_r($codcancel30daysRes); exit;
			$codcancel30day = $codcancel30daysRes['codcancel30day']; 
			
			$shipmentsCOD30day = "SELECT COUNT(sfs.`entity_id`) AS CODcount,sfs.`order_id`,sfs.`udropship_vendor`,sfop.`parent_id`,sfop.`method` FROM `sales_flat_shipment` sfs LEFT JOIN `sales_flat_order_payment` sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`udropship_vendor` = '".$vendorId."' AND sfop.`method` = 'cashondelivery' AND sfs.`created_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)";
			$shipmentsCOD30dayRes = $readconnMain->query($shipmentsCOD30day)->fetch();
			//print_r($shipmentsCOD30dayRes); exit;
			$codShipments30Day = $shipmentsCOD30dayRes['CODcount'];
			$codRatio = round($codShipments30Day/$shipments30day,1);  
			
			$sellerRating  = round(min((3/$refundratio90day),2) + min((2/$disputeratio90day),1.5) + min((2.5/$dispatchavg),1.5),1);   
			
			$readconnMain->closeConnection();

$connstats = Mage::getSingleton('core/resource')->getConnection('statsdb_connection');;			

$sellerQuality = "SELECT * FROM `seller_quality_craftsvilla` WHERE `vendor_id` = '".$vendorId."'"; 
$sellerQualityRes = $connstats->query($sellerQuality)->fetchAll();

	if($sellerQualityRes) 
	{

	$updateSellerQuality = "UPDATE `seller_quality_craftsvilla` SET `vendor_name`='".$vendorName."',`total_shipments_90_days`='".$shipments90day."',`total_shipments_30_days`='".$shipments30day."',`refund_ratio_90_days`='".$refundratio90day."',`refund_ratio_30_days`='".$refundratio30day."',`dispute_ratio_90_days`='".$disputeratio90day."',`dispute_ratio_30_days`='".$disputeratio30day."',`dispatch_prepaid_90_days`='".$dispatchavg."',`dispatch_prepaid_30_days`='".$dispatchavg30day."',`cod_return_90_days`='".$codreturn90day."',`cod_return_30_days`='".$codreturn30day."',`cod_cancel_90_days`='".$codcancel90day."',`cod_cancel_30_days`='".$codcancel30day."',`cod_ratio`='".$codRatio."',`craftsvilla_seller_rating`='".$sellerRating."' WHERE `vendor_id` = '".$vendorId."'";
		$updateSellerQualityRes = $connstats->query($updateSellerQuality);

	} 
	else 
	{

	$insertSellerQuality = "INSERT INTO `seller_quality_craftsvilla`( `vendor_id`, `vendor_name`, `total_shipments_90_days`, `total_shipments_30_days`, `refund_ratio_90_days`, `refund_ratio_30_days`, `dispute_ratio_90_days`, `dispute_ratio_30_days`, `dispatch_prepaid_90_days`, `dispatch_prepaid_30_days`, `cod_return_90_days`, `cod_return_30_days`, `cod_cancel_90_days`, `cod_cancel_30_days`, `cod_ratio`, `craftsvilla_seller_rating`) VALUES ($vendorId,'".$vendorName."',$shipments90day,$shipments30day,$refundratio90day,$refundratio30day,$disputeratio90day,$disputeratio30day,$dispatchavg,$dispatchavg30day,$codreturn90day,$codreturn30day,$codcancel90day,$codcancel30day,$codRatio,$sellerRating)";
		$insertSellerQualityRes = $connstats->query($insertSellerQuality);

	}
$connstats->closeConnection();
}
$k++;
} 

$summary = "Seller Quality Script Done. Total Updated:".$k;

 $mail = Mage::getModel('core/email');
                                $mail->setToName('Manoj');
                                $mail->setToEmail('manoj@craftsvilla.com');
                                $mail->setBody($summary);
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

?>

