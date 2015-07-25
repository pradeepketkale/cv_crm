<?php 
require_once 'app/Mage.php';
Mage::App();

$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$writecon = Mage::getSingleton('core/resource')->getConnection('core_write');

$postIdQuery = "SELECT * FROM `mktngproducts` WHERE `created_at` > DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY `mktngproducts_id` DESC";
$postIdQueryRes = $readcon->query($postIdQuery)->fetchAll();
//echo '<pre>';print_r($postIdQueryRes); exit;
echo "Total Products to Update:".sizeof($postIdQueryRes); 
$k = 0;
foreach($postIdQueryRes as $_postIdQueryRes) 
{
	if($k < 5) 
	{
	
	$fbPostId = $_postIdQueryRes['fb_post_id'];
	$sku = $_postIdQueryRes['product_sku'];

//----------------------------------------------------------FB CODE--------------------------------------------------------------------

	$json_url_likes ='https://graph.facebook.com/201477243197783_'.$fbPostId.'/likes?summary=true';
	$json_url_comments ='https://graph.facebook.com/201477243197783_'.$fbPostId.'/comments?summary=true';
	$json_url_shares ='https://graph.facebook.com/201477243197783_'.$fbPostId.'?fields=shares&access_token=CAACEdEose0cBAMGU8UsyWx8GC5QM00NZA17FjmTfj1LtTQpyCeIhvraMhctPjZC6zIs1f2i7bUDvGncpcybnhZAMWDbZCklwZAhhgMm4cYIcAasrwuEFZBSBjfDwkZBDcTXd2hZBpIB0Wx38WiEEkeiVyySqWAQ3GuHT2kvJ8ZCstfkMtZAhTfx5IrkKLaZCT6VHKAZD';

	$json_likes = file_get_contents($json_url_likes);
	//Extract the likes count from the JSON object
	$json_output_likes = json_decode($json_likes);
	$likesCount = $json_output_likes->summary->total_count;


	$json_comments = file_get_contents($json_url_comments);
	//echo '<pre>';print_r(json_decode($json_comments)); exit;
	$json_output_comments = json_decode($json_comments);
	$commentsCount = $json_output_comments->summary->total_count;

	 
	$json_shares = file_get_contents($json_url_shares);
	$json_output_shares = json_decode($json_shares);
	$sharesCount = $json_output_shares->shares->count; 
//--------------------------------------------------------------------------------------------------------------------------------------
	
	
	$productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".mysql_escape_string($sku)."'";
	$productQueryRes = $readcon->query($productQuery)->fetch();
echo 	$productId = $productQueryRes['entity_id'];   
	
	$product = Mage::helper('catalog/product')->loadnew($productId);
	//echo '<pre>'; print_r($product); exit;
	$vendorId = $product->getUdropshipVendor(); 
	$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
	$vendorName = $vendorInfo->getVendorName(); 
	//$vendorId = $vendorInfo->getVendorId();  
	$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
	//echo "<pre>"; print_r($stock->getData()); echo "</pre>";
	$product_Inventory = $stock->getQty(); 
	
$total90daysShipmentsQuery = "SELECT COUNT(*) AS shipmentcount FROM `sales_flat_shipment` WHERE `udropship_vendor`='".$vendorId."' AND `created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY)";
	$total90daysShipmentsQueryRes = $readcon->query($total90daysShipmentsQuery)->fetch();
	$shipmentCount = $total90daysShipmentsQueryRes['shipmentcount']; 
			
	
	$total90daysRefunfInititedQuery = "SELECT COUNT(*) AS refundInitiatedCount  FROM `sales_flat_shipment` WHERE `udropship_vendor`='".$vendorId."' AND `udropship_status` = 12 AND `created_at` > DATE_SUB(NOW(), INTERVAL 90 DAY)";
	$total90daysRefunfInititedQueryRes = $readcon->query($total90daysRefunfInititedQuery)->fetch();
	$refundInitiatedCount = $total90daysRefunfInititedQueryRes['refundInitiatedCount']; 
	$refundPercentage = round(($refundInitiatedCount/$shipmentCount)*100); 
		
	
	$diff = 0;
	$diffnum = 0;
	$dispatchTimeQuery = "SELECT s.created_at as createdat, s.updated_at as updatedat, p.method as paymethod, TIMESTAMPDIFF(DAY,s.created_at,s.updated_at) AS diff FROM `sales_flat_shipment` as s LEFT JOIN `sales_flat_order_payment` as p ON s.order_id = p.parent_id where `udropship_vendor`='".$vendorId."' AND p.method != 'cashondelivery' AND s.udropship_status = '1'  AND s.created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)";  
	$dispatchTimeQueryRes = $readcon->query($dispatchTimeQuery)->fetchAll();
  			//echo '<pre>'; print_r($dispatchTimeQueryRes); exit;
  			foreach($dispatchTimeQueryRes as $_dispatchTimeQueryRes)
  			{
	  			$dispatchTime = $_dispatchTimeQueryRes['diff']; 		 
				$diff += $dispatchTime;
				$diffnum++;
			}
			$dispatchavg = round($diff/$diffnum);
			
			
	date_default_timezone_set("Asia/Kolkata");
	$start24Hour = date("Y-m-d 00:00:00",strtotime(yesterday));     // last 24 hours sales
	$end24Hour = date("Y-m-d 23:59:59",strtotime(yesterday));


$onedayQuery = "SELECT COUNT(*) AS `productcount`,`created_at` FROM `sales_flat_order_item` WHERE `sku`= '".mysql_escape_string($sku)."' AND (`created_at` BETWEEN '".$start24Hour."' AND '".$end24Hour."')"; 


				$onedayQueryRes = $readcon->query($onedayQuery)->fetch();
				//print_r($onedayQueryRes); exit;
				$sale24Hours = $onedayQueryRes['productcount']; 
				
				$start7Day = date("Y-m-d 00:00:00",strtotime('-7 days'));       // last 7 days
				$end7Day = date("Y-m-d 00:00:00",strtotime('-1 second'));
$sevendaysQuery = "SELECT COUNT(*) AS `productcount`,`created_at` FROM `sales_flat_order_item` WHERE `sku`= '".mysql_escape_string($sku)."' AND (`created_at` BETWEEN '".$start7Day."' AND '".$end7Day."')";    

				$sevendaysQueryRes = $readcon->query($sevendaysQuery)->fetch();
				//print_r($sevendaysQueryRes); exit;
				$sale7Day =  $sevendaysQueryRes['productcount']; 
				
				$start30Day = date("Y-m-d 23:59:59", strtotime('-1 month'));    //last 30 days
				$end30Day = date("Y-m-d 00:00:00",strtotime('-1 second'));
$thirtydaysQuery = "SELECT COUNT(*) AS `productcount`,`created_at` FROM `sales_flat_order_item` WHERE `sku`= '".mysql_escape_string($sku)."' AND (`created_at` BETWEEN '".$start30Day."' AND '".$end30Day."')";    
				
				$thirtydaysQueryRes = $readcon->query($thirtydaysQuery)->fetch();
				$sale30Day = $thirtydaysQueryRes['productcount']; 
				
				
		echo "updating";
	$fblikesCountUpdateQuery = "UPDATE `mktngproducts` SET `sale_one_day`='".$sale24Hours."',`sale_seven_days`='".$sale7Day."',`sale_thirty_days`='".$sale30Day."',`fb_likes`='".$likesCount."',`fb_comments`='".$commentsCount."',`fb_shares`='".$sharesCount."',`product_inventory`='".$product_Inventory."',`refund_percentage`='".$refundPercentage."',`total_shipments`='".$shipmentCount."',`dispatch_time`='".$dispatchavg."' WHERE `product_sku`='".mysql_escape_string($sku)."'"; 


			   $fblikesCountUpdateQueryRes = $writecon->query($fblikesCountUpdateQuery);
		
	
	}
	echo $k++;
} 
			   $readcon->closeConnection();
			   $writecon->closeConnection(); 

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody("Script Number CVSCRSL001 name updateMarketingPrdFB_scr.php finished");
$mail->setSubject("Marketing FB Updated  (Total ".$k.") Script Finished at Time:".$currentTime);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();



?>

