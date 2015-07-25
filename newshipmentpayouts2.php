<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$msc=microtime(true);
$shipmentPayoutQuery = "SELECT * FROM `shipmentpayout` WHERE `shipmentpayout_created_time` <= DATE_SUB(NOW(),INTERVAL 365 DAY)"; 
$readshipmentQuery = $db->query($shipmentPayoutQuery)->fetchAll();
$resultshipments = $readshipmentQuery->fetch();
//var_dump($resultPrdcts);exit;
$j = 0;
//while($resultshipments = mysql_fetch_array($readshipmentQuery))
 foreach($readshipmentQuery as $resultshipments)
{
	$shipmentIdMaintable =  $resultshipments['shipmentpayout_id'];

	$readQuery = "select `shipmentpayout_id` from `shipmentpayout_s1` where `shipmentpayout_id` = '".$shipmentIdMaintable."'";
		$results = $db->query($readQuery);
		$id = $results->fetch();
		if(!$id[0])
			{
		$import = "INSERT INTO `shipmentpayout_s1`(`shipmentpayout_id`, `shipment_id`, `order_id`, `shipmentpayout_status`, `shipmentpayout_created_time`, `citibank_utr`, `shipmentpayout_update_time`, `payment_amount`, `couponcode`, `discount`, `commission_amount`, `type`, `intshipingcost`, `adjustment`, `comment`, `refundtodo`, `refunded_amount`) VALUES ('".$shipmentIdMaintable."','".$resultshipments['shipment_id']."','".$resultshipments['order_id']."','".$resultshipments['shipmentpayout_status']."','".$resultshipments['shipmentpayout_created_time']."','".$resultshipments['citibank_utr']."','".$resultshipments['shipmentpayout_update_time']."','".$resultshipments['payment_amount']."','".$resultshipments['couponcode']."','".$resultshipments['discount']."','".$resultshipments['commission_amount']."','".$resultshipments['type']."','".$resultshipments['intshipingcost']."','".$resultshipments['adjustment']."','".$resultshipments['comment']."','".$resultshipments['refundtodo']."','".$resultshipments['refunded_amount']."')";										  
			$db->query($import)->fetch();
   $deleteQueryofmain = "DELETE FROM `shipmentpayout` WHERE `shipmentpayout_id` = '".$shipmentIdMaintable."'";
	$db->query($deleteQueryofmain);
	echo 'Deleted the row of shipment id-'.$deleteQueryofmain."\n";
		    }
			else{
				$import = "UPDATE `shipmentpayout_s1` SET `shipmentpayout_id`='".$shipmentIdMaintable."',`shipment_id`='".$resultshipments['shipment_id']."',`order_id`='".$resultshipments['order_id']."',`shipmentpayout_status`='".$resultshipments['shipmentpayout_status']."',`shipmentpayout_created_time`='".$resultshipments['shipmentpayout_created_time']."',`citibank_utr`='".$resultshipments['citibank_utr']."',`shipmentpayout_update_time`='".$resultshipments['shipmentpayout_update_time']."',`payment_amount`='".$resultshipments['payment_amount']."',`couponcode`='".$resultshipments['couponcode']."',`discount`='".$resultshipments['discount']."',`commission_amount`='".$resultshipments['commission_amount']."',`type`='".$resultshipments['type']."',`intshipingcost`='".$resultshipments['intshipingcost']."',`adjustment`='".$resultshipments['adjustment']."',`comment`='".$resultshipments['comment']."',`refundtodo`='".$resultshipments['refundtodo']."',`refunded_amount`='".$resultshipments['refunded_amount']."' WHERE `shipmentpayout_id` = '".$shipmentIdMaintable."'";
				$db->query($import)->fetch();
				}
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds
