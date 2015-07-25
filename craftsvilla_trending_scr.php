<?php
require_once 'app/Mage.php';
Mage::app();

$hlp = Mage::helper('generalcheck');
$mainconn = $hlp->getMaindbconnection();

$result_trending = array();
$wish_product_query = mysql_query("SELECT `product_id` FROM `wishlist_item` WHERE `wishlist_id` = (SELECT `wishlist_id` FROM `wishlist` WHERE `customer_id` = (SELECT `entity_id` FROM `customer_entity` WHERE `email`='wish@craftsvilla.com')) ",$mainconn);
mysql_close($mainconn);

	while($wishProductHome = mysql_fetch_array($wish_product_query))
	{

		array_push($result_trending,$wishProductHome['product_id']);

	}

$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$queryTrendingQuery =  "SELECT `sku`,`product_id`, count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 1 DAY) GROUP BY `sku` ORDER BY count1 DESC LIMIT 240";

$getTrendingResult = $readConn->query($queryTrendingQuery)->fetchAll();

foreach($getTrendingResult as $_getTrendingResult1)
	{

		array_push($result_trending,$_getTrendingResult1['product_id']);

	}

$readConn->closeConnection();

//echo '<pre>'; print_r($result_trending); exit;
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
//		$product_id = $_getTrendingResult;
//		$insertTrendingQuery = mysql_query("INSERT INTO `craftsvilla_trending` (`product_id`) VALUES ('".$product_id."')",$statsconn);
 		$product_id = $_getTrendingResult;
		$readConn1 = Mage::getSingleton('core/resource')->getConnection('core_read');
		$resultQuery = "SELECT `category_id2`,`cod` FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$product_id."'";
		$resultProduct = $readConn1->query($resultQuery)->fetch();
		$categoryId = $resultProduct['category_id2'];
		$isCod = $resultProduct['cod'];
		$readConn1->closeConnection();
                $getloadProductData = Mage::helper('catalog/product')->loadnew($product_id);
                $getprice = $getloadProductData->getPrice();
		$getVendor = $getloadProductData->getUdropshipVendor();
                $insertTrendingQuery = mysql_query("INSERT INTO `craftsvilla_trending` (`product_id`,`min_price`,`udropship_vendor`,`categoryid`,`cod`) VALUES ('".$product_id."','".$getprice."','".$getVendor."','".$categoryId."','".$isCod."')",$statsconn);

	} 

	mysql_close($statsconn);



//---------------------
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody("Script Number CVSCRCH001 name craftsvilla_trending.php finished");
$mail->setSubject("Craftsvilla Trending Script Finished at Time:".$currentTime);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

?>



