<?php
require_once 'app/Mage.php';
Mage::app();

$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');

$queryTrendingQuery =  "SELECT `sku`,`product_id`, count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 600 DAY) GROUP BY `sku` ORDER BY count1 DESC LIMIT 240";

$getTrendingResult = $readConn->query($queryTrendingQuery)->fetchAll();

$result_trending = array();
foreach($getTrendingResult as $_getTrendingResult1)
	{

		array_push($result_trending,$_getTrendingResult1['product_id']);

	}

$readConn->closeConnection();

$hlp = Mage::helper('generalcheck');
$statsconn = $hlp->getStatsdbconnection();

$queryProductHome = mysql_query("SELECT `entity_id` FROM `catalog_product_craftsvilla3` WHERE `visibility` IN (1,4) AND (`category_id1` ='991' OR `category_id2` = '991' OR `category_id3` = '991' OR `category_id4` = '991')",$statsconn);
	while($resultProductHome = mysql_fetch_array($queryProductHome))
	{

		array_push($result_trending,$resultProductHome['entity_id']);

	}

$num_rows = mysql_query("select * from `craftsvilla_trending`",$statsconn);
$num_rows_values = mysql_num_rows($num_rows);

	if($num_rows_values != 0)
	{
		$clearTrendingQuery = mysql_query("TRUNCATE TABLE `craftsvilla_trending`",$statsconn);
	}
	$result_trending = array_unique($result_trending);
	foreach($result_trending as $_getTrendingResult)
	{
	
		$product_id = $_getTrendingResult;
		$getloadProductData = Mage::helper('catalog/product')->loadnew($product_id);
		$getprice = $getloadProductData->getPrice();
		$insertTrendingQuery = mysql_query("INSERT INTO `craftsvilla_trending` (`product_id`,`min_price`) VALUES ('".$product_id."','".$getprice."')",$statsconn);

	} 

	mysql_close($statsconn);


?>

