<?php
require_once 'app/Mage.php';
Mage::app();

$hlp = Mage::helper('generalcheck');
$mainconn = $hlp->getMaindbconnection();

$result_trending = array();

$readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
$queryTrendingQuery =  "SELECT 	product_sku FROM `mktngproducts` ORDER BY `mktngproducts_id` DESC LIMIT 240";
$getTrendingResult = $readConn->query($queryTrendingQuery)->fetchAll();

$readConn->closeConnection();

foreach($getTrendingResult as $_getTrendingResult1)
    {
array_push($result_trending,$_getTrendingResult1['product_sku']);
   }


$hlp = Mage::helper('generalcheck');
$statsconn = $hlp->getStatsdbconnection();

$num_rows = mysql_query("select * from `craftsvilla_newarrival`",$statsconn);
$num_rows_values = mysql_num_rows($num_rows);

	if($num_rows_values != 0)
	{
		$clearTrendingQuery = mysql_query("TRUNCATE TABLE `craftsvilla_newarrival`",$statsconn);
	}

	$result_trending = array_unique($result_trending);
//	print_r($result_trending); exit;

	foreach($result_trending as $_getTrendingResult)
	{
		$readConn1 = Mage::getSingleton('core/resource')->getConnection('core_read');
		$getproductQuery = "SELECT * from catalog_product_entity where `sku` = '".mysql_real_escape_string($_getTrendingResult)."'";
		$getproductQueryRes = $readConn1->query($getproductQuery)->fetch(); 
		echo $product_id = $getproductQueryRes['entity_id']; 

		$resultQuery = "SELECT `category_id2`,`cod` FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$product_id."'";
		$resultProduct = $readConn1->query($resultQuery)->fetch();
		$categoryId = $resultProduct['category_id2'];
		$isCod = $resultProduct['cod'];
		$readConn1->closeConnection();
		if($categoryId)
		{
                $getloadProductData = Mage::helper('catalog/product')->loadnew($product_id);
                $getprice = $getloadProductData->getPrice();
		echo $getVendor = $getloadProductData->getUdropshipVendor();
                $insertTrendingQuery = mysql_query("INSERT INTO `craftsvilla_newarrival` (`product_id`,`min_price`,`udropship_vendor`,`categoryid`,`cod`) VALUES ('".$product_id."','".$getprice."','".$getVendor."','".$categoryId."','".$isCod."')",$statsconn);
		}
	} 

	mysql_close($statsconn);



//---------------------
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody("Script Number CVSCRCH009 name craftsvilla_newarrival_scr.php finished");
$mail->setSubject("Craftsvilla New Arrival Script Finished at Time:".$currentTime);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

?>



