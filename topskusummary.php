<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
$arrayOfSkus = toGetTopskus1();
$skuToplistHtml .= '<h1 style="font-size: 30px;height: 20px;padding: 12px;vertical-align:top;background:#F2F2F2;color:#CE3D49;width:500px;">Top Sales Sku List Of 1 Days</h1>';

$skuToplistHtml .= "<table><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>VendorName</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Price(Rupees)</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Current Inventory</td></tr>";

foreach($arrayOfSkus as $_arrayOfSkus)
	{
	$typeIdnskuquery = "SELECT * FROM `catalog_product_entity` where `sku` = '".$_arrayOfSkus."'";
	$resultofskunidtype = $readId>query($typeIdnskuquery)->fetchAll();
		//while($_resultofskunidtype = mysql_fetch_array($resultofskunidtype))
		 foreach($resultofskunidtype as $_resultofskunidtype)
		{
		$prdId = $_resultofskunidtype['entity_id'];
		// To get Produt link		
		$getProductdetail = Mage::getModel('catalog/product')->load($prdId);
		$prdLink = $getProductdetail->getProductUrl();
		$getPrice = round($getProductdetail->getPrice());
		$prodImage=Mage::helper('catalog/image')->init($getProductdetail, 'image')->resize(166, 166);
		$getUdropshipVemdor = $getProductdetail->getUdropshipVendor();
		$attrquery = "SELECT * FROM `udropship_vendor` WHERE `vendor_id` = '".$getUdropshipVemdor."'";
		$resultAttrInt = $readId>query($attrquery)->fetchAll();
		//while($_resultAttrInt = mysql_fetch_array($resultAttrInt))
		foreach($resultAttrInt as $_resultAttrInt)
			{
				$vndName = mysql_escape_string($_resultAttrInt['vendor_name']);
 				 $vurl = Mage::getBaseUrl().$_resultAttrInt['url_key'];
			}	
			
	//check inventory `is_in_stock`
	$catStockItem = "SELECT `qty`,`is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$prdId."'";
	$resultcatStockItem = $readId>query($catStockItem)->fetchAll();
	
	//while($_resultcatStockItem = mysql_fetch_array($resultcatStockItem))
	foreach($resultcatStockItem as $_resultcatStockItem)
		{	
			$currentInventory = round($_resultcatStockItem['qty']);
		}
	}


$skuToplistHtml .= "<tr><td><a href=".$prdLink." target=_blank> <img src=".$prodImage." /></a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_arrayOfSkus."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>
<a href=".$vurl." target=_blank>".$vndName."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$getPrice."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currentInventory."</td></tr>";
}

$arrayOfSkus7 = toGetTopskus7();

$skuToplistHtml .= '</table><br><br><br> <h1 style="font-size: 30px;height: 20px;padding: 12px;vertical-align:top;background:#F2F2F2;color:#CE3D49;width:500px;">Top Sales Sku List Of 7 Days</h1> <table><tr><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Image</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">SKU</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">VendorName</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Product Price(Rupees)</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Current Inventory</td></tr>';

foreach($arrayOfSkus7 as $_arrayOfSkus7)
	{
	$typeIdnskuquery7 = "SELECT * FROM `catalog_product_entity` where `sku` = '".$_arrayOfSkus7."'";
	$resultofskunidtype7 = $readId>query($typeIdnskuquery7)->fetchAll();
		//while($_resultofskunidtype7 = mysql_fetch_array($resultofskunidtype7))
		foreach($resultofskunidtype7 as $_resultofskunidtype7)
		{
		$prdId = $_resultofskunidtype7['entity_id'];
		// To get Produt link		
		$getProductdetail = Mage::getModel('catalog/product')->load($prdId);
		$prdLink = $getProductdetail->getProductUrl();
		$getPrice = round($getProductdetail->getPrice());
		$prodImage=Mage::helper('catalog/image')->init($getProductdetail, 'image')->resize(166, 166);
		$getUdropshipVemdor = $getProductdetail->getUdropshipVendor();
		$attrquery = "SELECT * FROM `udropship_vendor` WHERE `vendor_id` = '".$getUdropshipVemdor."'";
		$resultAttrInt = $readId>query($attrquery)->fetchAll();
		//while($_resultAttrInt = mysql_fetch_array($resultAttrInt))
		foreach($resultAttrInt as $_resultAttrInt)
			{
				$vndName = mysql_escape_string($_resultAttrInt['vendor_name']);
 				 $vurl = Mage::getBaseUrl().$_resultAttrInt['url_key'];
			}	
			
	//check inventory `is_in_stock`
	$catStockItem = "SELECT `qty`,`is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$prdId."'";
	$resultcatStockItem = $readId>query($catStockItem)->fetchAll();
	
	//while($_resultcatStockItem = mysql_fetch_array($resultcatStockItem))
	foreach($resultcatStockItem as $_resultcatStockItem)
		{	
			$currentInventory = round($_resultcatStockItem['qty']);
		}
	}

$skuToplistHtml .= "<tr><td><a href=".$prdLink." target=_blank> <img src=".$prodImage." /></a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_arrayOfSkus7."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>
<a href=".$vurl." target=_blank>".$vndName."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$getPrice."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currentInventory."</td></tr>";
}

$arrayOfSkus30 = toGetTopskus30();

$skuToplistHtml .= '</table><br><br><br> <h1 style="font-size: 30px;height: 20px;padding: 12px;vertical-align:top;background:#F2F2F2;color:#CE3D49;width:500px;">Top Sales Sku List Of 30 Days</h1> <table><tr><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Image</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">SKU</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">VendorName</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Product Price(Rupees)</td><td style="font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">Current Inventory</td></tr>';

foreach($arrayOfSkus30 as $_arrayOfSkus30)
	{
	$typeIdnskuquery30 = "SELECT * FROM `catalog_product_entity` where `sku` = '".$_arrayOfSkus30."'";
	$resultofskunidtype30 = $readId>query($typeIdnskuquery30)->fetchAll();
		//while($_resultofskunidtype30 = mysql_fetch_array($resultofskunidtype30))
		foreach($resultofskunidtype30 as $_resultofskunidtype30)
		{
		$prdId = $_resultofskunidtype30['entity_id'];
		// To get Produt link		
		$getProductdetail = Mage::getModel('catalog/product')->load($prdId);
		$prdLink = $getProductdetail->getProductUrl();
		$getPrice = round($getProductdetail->getPrice());
		$prodImage=Mage::helper('catalog/image')->init($getProductdetail, 'image')->resize(166, 166);
		$getUdropshipVemdor = $getProductdetail->getUdropshipVendor();
		$attrquery = "SELECT * FROM `udropship_vendor` WHERE `vendor_id` = '".$getUdropshipVemdor."'";
		$resultAttrInt = $readId>query($attrquery)->fetchAll();
		//while($_resultAttrInt = mysql_fetch_array($resultAttrInt))
		foreach($resultAttrInt as $_resultAttrInt)
			{
				$vndName = mysql_escape_string($_resultAttrInt['vendor_name']);
 				 $vurl = Mage::getBaseUrl().$_resultAttrInt['url_key'];
			}	
			
	//check inventory `is_in_stock`
	$catStockItem = "SELECT `qty`,`is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$prdId."'";
	$resultcatStockItem = $readId>query($catStockItem)->fetchAll();
	
	//while($_resultcatStockItem = mysql_fetch_array($resultcatStockItem))
	foreach($resultcatStockItem as $_resultcatStockItem)
		{	
			$currentInventory = round($_resultcatStockItem['qty']);
		}
	}

$skuToplistHtml .= "<tr><td><a href=".$prdLink." target=_blank> <img src=".$prodImage." /></a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_arrayOfSkus30."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>
<a href=".$vurl." target=_blank>".$vndName."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$getPrice."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currentInventory."</td></tr>";
}




//echo $skuToplistHtml;
//exit;
$message = "Top SKU Summary. Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
//$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($skuToplistHtml);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Tech");
$mail->setType('html');
$mail->send();
$mail->setToEmail('monica@craftsvilla.com');	
$mail->send();

echo 'Email sent Successfully';


function toGetTopskus1(){


//$readId = mysql_connect("newserver2.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
//mysql_select_db("nzkrqvrxme", $readId);
$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
	$queryofgetSkus = "SELECT sku,count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 1 DAY) GROUP BY `sku` ORDER BY count1 DESC LIMIT 25";
	$resultofgetSkus = $readId>query($queryofgetSkus)->fetchAll();
	$i = 0;	
	
	//while($_resultofgetSkus = mysql_fetch_array($resultofgetSkus)){
	foreach($resultofgetSkus as $_resultofgetSkus){
		 	$sku[$i] = $_resultofgetSkus['sku'];
			$countSkuOrders[$i] = $_resultofgetSkus['count1'];
			$i++;			
			
	}
	return $sku;
	
}

function toGetTopskus7(){


//$readId = mysql_connect("newserver2.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
//mysql_select_db("nzkrqvrxme", $readId);
$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
	$queryofgetSkus = "SELECT sku,count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 7 DAY) GROUP BY `sku` ORDER BY count1 DESC LIMIT 25";
	$resultofgetSkus = $readId>query($queryofgetSkus)->fetchAll();
	$i = 0;	
	
	//while($_resultofgetSkus = mysql_fetch_array($resultofgetSkus)){
	foreach($resultofgetSkus as $_resultofgetSkus){
	
		 	$sku[$i] = $_resultofgetSkus['sku'];
			$countSkuOrders[$i] = $_resultofgetSkus['count1'];
			$i++;			
			
	}
	return $sku;
	
}

function toGetTopskus30(){

 

$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
	$queryofgetSkus = "SELECT sku,count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 30 DAY) GROUP BY `sku` ORDER BY count1 DESC LIMIT 25";
	$resultofgetSkus = $readId>query($queryofgetSkus)->fetchAll();
	$i = 0;	
	
	while($_resultofgetSkus = mysql_fetch_array($resultofgetSkus)){
	foreach($resultofgetSkus as $_resultofgetSkus){
	
		 	$sku[$i] = $_resultofgetSkus['sku'];
			$countSkuOrders[$i] = $_resultofgetSkus['count1'];
			$i++;			
			
	}
	return $sku;
}

