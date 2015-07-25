<?php 
require_once 'app/Mage.php';
Mage::App();

$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$writecon = Mage::getSingleton('core/resource')->getConnection('core_write');

$postIdQuery = "SELECT * FROM `mktngproducts` WHERE `created_at` > DATE_SUB(NOW(), INTERVAL 30 DAY)";
$postIdQueryRes = $readcon->query($postIdQuery)->fetchAll();
//echo '<pre>';print_r($postIdQueryRes); exit;

foreach($postIdQueryRes as $_postIdQueryRes) 
{
	$fbPostId = $_postIdQueryRes['fb_post_id'];
	$sku = $_postIdQueryRes['product_sku'];

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
	
	$productQuery = "SELECT * FROM `catalog_product_entity` WHERE `sku` = '".$sku."'";
	$productQueryRes = $readcon->query($productQuery)->fetch();
	$productId = $productQueryRes['entity_id']; 
	$product = Mage::helper('catalog/product')->loadnew($productId);
	//echo '<pre>'; print_r($product); exit;
	$vendorId = $product->getUdropshipVendor(); 
	$vendorInfo =Mage::getModel('udropship/vendor')->load($vendorId);
	$vendorName = $vendorInfo->getVendorName(); 
	//$vendorId = $vendorInfo->getVendorId();  

	date_default_timezone_set("Asia/Kolkata");
	$start24Hour = date("Y-m-d 00:00:00",strtotime(yesterday));     // last 24 hours
	$end24Hour = date("Y-m-d 23:59:59",strtotime(yesterday));

$onedayQuery = "SELECT `created_at` , `base_total_value` FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendorId."' AND (`created_at` BETWEEN '".$start24Hour."' AND '".$end24Hour."')";
				$baseTotal24Hours = 0;
				$onedayQueryRes = $readcon->query($onedayQuery)->fetchAll();
				
				foreach($onedayQueryRes as $_onedayQueryRes)
				{ 
					$baseTotal24Hours += $_onedayQueryRes['base_total_value']; 
				}
				
	
				$start7Day = date("Y-m-d 00:00:00",strtotime('-7 days'));       // last 7 days
				$end7Day = date("Y-m-d 00:00:00",strtotime('-1 second'));
$sevendaysQuery = "SELECT `created_at` , `base_total_value` FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendorId."' AND (`created_at` BETWEEN '".$start7Day."' AND '".$end7Day."')";   

				$baseTotal7Days = 0;
				$sevendaysQueryRes = $readcon->query($sevendaysQuery)->fetchAll();
				
				foreach($sevendaysQueryRes as $_sevendaysQueryRes)
				{ 
					$baseTotal7Days += $_sevendaysQueryRes['base_total_value']; 
				}
				
				$start30Day = date("Y-m-d 23:59:59", strtotime('-1 month'));    //last 30 days
				$end30Day = date("Y-m-d 00:00:00",strtotime('-1 second'));
$thirtydaysQuery = "SELECT `created_at` , `base_total_value` FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendorId."' AND (`created_at` BETWEEN '".$start30Day."' AND '".$end30Day."')";   
				$baseTotal30Days = 0;
				$thirtydaysQueryRes = $readcon->query($thirtydaysQuery)->fetchAll();
				
				foreach($thirtydaysQueryRes as $_thirtydaysQueryRes)
				{ 
					$baseTotal30Days += $_thirtydaysQueryRes['base_total_value']; 
				}
				
	
$fblikesCountUpdateQuery = "UPDATE `mktngproducts` SET `sale_one_day`='".$baseTotal24Hours."',`sale_seven_days`='".$baseTotal7Days."',`sale_thirty_days`='".$baseTotal30Days."',`fb_likes`='".$likesCount."',`fb_comments`='".$commentsCount."',`fb_shares`='".$sharesCount."' WHERE `fb_post_id`='".$fbPostId."'";
			   $fblikesCountUpdateQueryRes = $writecon->query($fblikesCountUpdateQuery);
			   $readcon->closeConnection();
			   $writecon->closeConnection(); 
}

?>
