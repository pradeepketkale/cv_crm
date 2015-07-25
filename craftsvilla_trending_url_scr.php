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

	$result_trending = array_unique($result_trending);
	$product_url = array();

	foreach($result_trending as $_getTrendingResult)
	{
 		$product_id = $_getTrendingResult;
                $getloadProductData = Mage::helper('catalog/product')->loadnew($product_id);
		array_push($product_url, $getloadProductData->getProductUrl());
	} 

		foreach($product_url as $_product_url)
		{ 
			$message .= $_product_url."<br>";
			
		}

mysql_close($statsconn);


//---------------------
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject("Craftsvilla Trending Script Finished at Time:".$currentTime);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

?>



