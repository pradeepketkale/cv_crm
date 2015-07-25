<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$msc=microtime(true);
$productsQuery = "SELECT `e`.`sku`,`e`.`entity_id` ,IF(_table_status.value_id > 0, _table_status.value, _table_status_default.value) AS `status`, IF(_table_visibility.value_id > 0, _table_visibility.value, _table_visibility_default.value) AS `visibility` FROM `catalog_product_entity` AS `e` INNER JOIN `catalog_product_entity_int` AS `_table_status_default` ON (`_table_status_default`.`entity_id` = `e`.`entity_id`) AND (`_table_status_default`.`attribute_id` = '80') AND `_table_status_default`.`store_id` = 0 LEFT JOIN `catalog_product_entity_int` AS `_table_status` ON (`_table_status`.`entity_id` = `e`.`entity_id`) AND (`_table_status`.`attribute_id` = '80') AND (`_table_status`.`store_id` = '1') INNER JOIN `catalog_product_entity_int` AS `_table_visibility_default` ON (`_table_visibility_default`.`entity_id` = `e`.`entity_id`) AND (`_table_visibility_default`.`attribute_id` = '85') AND `_table_visibility_default`.`store_id` = 0 LEFT JOIN `catalog_product_entity_int` AS `_table_visibility` ON (`_table_visibility`.`entity_id` = `e`.`entity_id`) AND (`_table_visibility`.`attribute_id` = '85') AND (`_table_visibility`.`store_id` = '1') WHERE (IF(_table_status.value_id > 0, _table_status.value, _table_status_default.value) = '1') AND (IF(_table_visibility.value_id > 0, _table_visibility.value, _table_visibility_default.value) = '4') ORDER BY `e`.`entity_id` DESC, `e`.`entity_id` DESC ";
$readPrdQuery = $db->query($productsQuery)->fetchAll();
//$resultPrdcts = mysql_fetch_array($readPrdQuery);
$j = 0;
//while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)
{
//	var_dump($resultPrdcts); 
		$prdId =  $resultPrdcts['entity_id'];
		$sku = $resultPrdcts['sku'];
		$readQuery = "select `product_id` from `catalog_product_vendor` where `product_id` = '".$prdId."'";
		$results = $db->query($readQuery);
		$id = $results->fetch();
//		echo $id[0];
			if(!$id[0])
			{
			//$prod = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
			//$vendorId = $prod->getUdropshipVendor();
			$prod = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 531";
			$vendorresults = $db->query($prod);
			$vendor = $vendorresults->fetch();
			$import = "INSERT into catalog_product_vendor(product_id,udropship_vendor)values('".$prdId."','".$vendor['value']."')";
			$db->query($import)->fetch();
		
			}
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds

