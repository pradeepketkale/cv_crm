<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db =  Mage::getSingleton('core/resource')->getConnection('core_read');
$countQuery = "SELECT count(`entity_id`) as countIndex FROM `catalog_product_index_price`";

$rsltCountQuery = $db->query($countQuery);
$countCatQuery = "SELECT count(`product_id`) as countCatIndex FROM `catalog_category_product_index`";

$rsltCountCatQuery = $db->query($countCatQuery);

$_countIndex = $rsltCountQuery->fetch(); 
$_countCatIndex = $rsltCountCatQuery->fetch();echo "<pre>"; 
echo $_countIndex[0];
echo $_countCatIndex[0];
if (($_countIndex[0] < 100000) || ($_countCatIndex[0] < 100000))
{
echo $message = "Failed:Adding and Updating Catalog Craftsvilla3:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
exit;
}
else
{
echo $message = "Successful and Started:Adding and Updating Catalog Craftsvilla3:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

}
$msc=microtime(true);
$productsQuery = "SELECT `e`.*,cat_index.store_id AS store_id ,cat_index.visibility AS visibility ,price_index.customer_group_id AS customer_group_id ,price_index.website_id AS website_id ,`cat_index`.`position` AS `cat_index_position`, price_index.price AS `indexed_price`,  `price_index`.`price`,  `price_index`.`final_price`, `price_index`.`min_price` AS `minimal_price`, `price_index`.`min_price`, `price_index`.`max_price`, `price_index`.`tier_price` FROM `catalog_product_entity` AS `e`
INNER JOIN `catalog_category_product_index` AS `cat_index` 
ON cat_index.product_id=e.entity_id AND cat_index.store_id='1' AND cat_index.visibility IN(1, 2, 4) AND cat_index.category_id='2'
INNER JOIN `catalog_product_index_price` AS `price_index` 
ON price_index.entity_id = e.entity_id AND price_index.website_id = '1' AND price_index.customer_group_id = 0 AND e.updated_at > DATE_SUB(NOW(),INTERVAL 1000 DAY)
ORDER BY `e`.`entity_id` desc  ";
//$readPrdQuery = mysql_query($productsQuery);
$readPrdQuery = $db->query($productsQuery)->fetchAll();
//$resultPrdcts = mysql_fetch_array($readPrdQuery);
$j = 0;
$k = 0;
 //while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)

{
	if($k < 10000000)
	{
	//var_dump($resultPrdcts);exit ;
echo		$prdId =  $resultPrdcts['entity_id'];
		$_productC = "SELECT * FROM `catalog_category_product_index` WHERE `product_id` = '".$resultPrdcts['entity_id']."'";
		//$readCrdQuery = mysql_query($_productC);
		$readCrdQuery = $db->query($_productC)->fetchAll();
			$value = array();
			$k = 0;
			$valueId[0] ='';
			$valueId[1] ='';
			$valueId[2] ='';
			$valueId[3] ='';
			//while($resultCategs = mysql_fetch_array($readCrdQuery)){
			 foreach($readCrdQuery as $resultCategs)
			$valueId[$k++] = $resultCategs['category_id'];
			//$value[] = $category->getName();
			}
		$categoryId1 = $valueId[0];
		$categoryId2 = $valueId[1];
		$categoryId3 = $valueId[2]; 
		$categoryId4 = $valueId[3];
		
		$readQuery = "select `entity_id` from `catalog_product_craftsvilla3` where `entity_id` = '".$prdId."'";
		//$results = mysql_query($readQuery);
		$results = $db->query($readQuery);
		$id = $results->fetch();
		 $prod = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 531";
			//$vendorresults = mysql_query($prod);
			$vendorresults = $db->query($prod);
			$vendor = $vendorresults->fetch();
			$obj1 = "custom_vars_combined";
			$vendorData = "SELECT `zip`,`".$obj1."`FROM `udropship_vendor` WHERE `vendor_id` = '".$vendor['value']."'";
			//$resultData = mysql_query($vendorData);
			$resultData = $db->query($vendorData);
			$vendor_row = $resultData->fetch();
			//for getting discount
			$orginalPrice = "SELECT * FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 60";
			//$orginalPriceresults = mysql_query($orginalPrice);
			$orginalPriceresults = $db->query($orginalPrice);
			$exactPrice = $orginalPriceresults->fetch();
			$setexactPrice = $exactPrice['value'];
			$specialPrice = "SELECT * FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 61";
			//$specialPriceresults = mysql_query($specialPrice);
			$specialPriceresults = $db->query($specialPrice);
			$specialExactPrice = $specialPriceresults->fetch();
			$setspecialPrice = $specialExactPrice['value'];
			$finalDiscPrice = '';
			if(!empty($setspecialPrice))
				{
				$perPrice = ((($setexactPrice-$setspecialPrice)/$setexactPrice)*100);
				if($perPrice < 0)
				 	{
					 $finalDiscPrice = 0;
					}
				else {
					$finalDiscPrice = round($perPrice);		
					}
				
				}
			//get shipment time
			$datediffcnQuery = "SELECT AVG(DATEDIFF(`updated_at`,`created_at`)) AS `avgdatediff` FROM `sales_flat_shipment` WHERE `udropship_vendor` = '".$vendor['value']."' AND `udropship_status` IN ('1','7') AND `updated_at` > DATE_SUB(NOW(),INTERVAL 180 DAY)";
			//$datediffcnresult = mysql_query($datediffcnQuery);
			$datediffcnresult = $db->query($datediffcnQuery);	
			$datediffcnresultget = $datediffcnresult->fetch();
			$exactIntervalday1 = number_format(($datediffcnresultget['avgdatediff']),1);
			$exactIntervalday = '';
			if($exactIntervalday1 > '0.5'){ 
				$exactIntervalday = $exactIntervalday1;
				}
			else{
				$exactIntervalday = 100;
				}
			//echo '<pre>';
			//print_r($sql_row);exit;
			$zip = $vendor_row['0'];
			$dataCodd = Zend_Json::decode($vendor_row[1]);
//			var_dump($dataCodd);exit;
			$paymentMthd = $dataCodd['vendoradmin_payment_methods'][0];
			
			if ($paymentMthd == 'cashondelivery')
				{
					$codMthd = '0';
				}
			else
				{
					$codMthd = '1';
				}
//get the name & description of product
		$productName = "SELECT * FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 56";
		//$productnameresults = mysql_query($productName);
		$productnameresults = $db->query($productName);
		$productnameresultsdata = $productnameresults->fetch();
		$_productName = $productnameresultsdata['value'];

		$productDesc = "SELECT * FROM `catalog_product_entity_text` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 57";
		//$productdescresults = mysql_query($productDesc);
		$productdescresults = $db->query($productDesc);
		$productdescresultsdata = $productdescresults->fetch();
		$_productDesc = $productdescresultsdata['value'];

$colorchoices = array('red', 'green','white','black','yellow','magenta','purple','grey','blue','brown','silver','beige','gold','multicolour');
                $color = findstringmatch(strtolower($_productName),$colorchoices);
                if (empty($color)) $color = findstringmatch(strtolower($_productDesc),$colorchoices);
				if (empty($color)) $color = 'Multicolor';

$connstats = Mage::getSingleton('core/resource')->getConnection('statsdb_connection');;
$querySellerRating = "SELECT `craftsvilla_seller_rating` FROM `seller_quality_craftsvilla` WHERE `vendor_id` = '".$vendor['value']."'";
$SellerRatingresult = $connstats->query($querySellerRating)->fetch();
$ratingData = $SellerRatingresult['craftsvilla_seller_rating'];
$connstats->closeConnection();


if(!$id[0])
{
$import = "INSERT INTO
`catalog_product_craftsvilla3`(`entity_id`,`entity_type_id`,`attribute_set_id`,`type_id`,`sku`,`created_at`,`updated_at`,`has_options`,`required_options`,
`price`, `minimal_price`, `min_price`, `max_price`,
`tier_price`,`discount`,`shippingtime`, `website_id`,
`customer_group_id`, `visibility`, `category_id1`, `category_id2`,
`category_id3`,`category_id4`, `store_id`, `cod`, `zip`,
`udropship_vendor`,`color`,`craftsvilla_seller_rating`)
 VALUES ('".$prdId."','".$resultPrdcts['entity_type_id']."','".$resultPrdcts['attribute_set_id']."','".$resultPrdcts['type_id']."','".mysql_real_escape_string($resultPrdcts['sku'])."','".$resultPrdcts['created_at']."','".$resultPrdcts['updated_at']."','".$resultPrdcts['has_options']."','".$resultPrdcts['required_options']."','".$resultPrdcts['price']."','".$resultPrdcts['minimal_price']."','".$resultPrdcts['min_price']."','".$resultPrdcts['max_price']."','".$resultPrdcts['tier_price']."','".$finalDiscPrice."','".$exactIntervalday."','".$resultPrdcts['website_id']."','".$resultPrdcts['customer_group_id']."','".$resultPrdcts['visibility']."','".$categoryId1."','".$categoryId2."','".$categoryId3."','".$categoryId4."','".$resultPrdcts['store_id']."','".$codMthd."','".$zip."','".$vendor['value']."','".$color."','".$ratingData."')";
//mysql_query($import) or die(mysql_error());
$db->query($import);
   }
else{
$import = "UPDATE `catalog_product_craftsvilla3` SET
`entity_id`='".$prdId."',`entity_type_id` =
'".$resultPrdcts['entity_type_id']."',`attribute_set_id` =
'".$resultPrdcts['attribute_set_id']."',`type_id` =
'".$resultPrdcts['type_id']."',`sku` =
'".mysql_real_escape_string($resultPrdcts['sku'])."',`created_at` =
'".$resultPrdcts['created_at']."',`updated_at` =
'".$resultPrdcts['updated_at']."', `has_options` =
'".$resultPrdcts['has_options']."',`required_options` =
'".$resultPrdcts['required_options']."',`price`='".$resultPrdcts['price']."',`minimal_price`='".$resultPrdcts['minimal_price']."',`min_price`='".$resultPrdcts['min_price']."',`max_price`='".$resultPrdcts['max_price']."',`tier_price`='".$resultPrdcts['tier_price']."',`discount`='".$finalDiscPrice."',`shippingtime`='".$exactIntervalday."',`website_id`='".$resultPrdcts['website_id']."',`customer_group_id`='".$resultPrdcts['customer_group_id']."',`visibility`='".$resultPrdcts['visibility']."',`category_id1`='".$categoryId1."',`category_id2`='".$categoryId2."',`category_id3`='".$categoryId3."',`category_id4`='".$categoryId4."',`store_id`='".$resultPrdcts['store_id']."',`cod`='".$codMthd."',`zip`='".$zip."',`udropship_vendor`='".$vendor['value']."',`color`='".$color."',`craftsvilla_seller_rating`='".$ratingData."'
WHERE `catalog_product_craftsvilla3`.`entity_id` ='".$prdId."'";
//mysql_query($import) or die(mysql_error());
$db->query($import);
}

}
$k++;
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds
function findstringmatch($haystack, $needles)
{
   foreach($needles as $needle) {
                $res = strpos($haystack, strtolower($needle));
                if ($res !== false) return $needle;
        }
        return "";
}

