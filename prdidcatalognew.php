<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$msc=microtime(true);
$productsQuery = "SELECT `e`.*,cat_index.store_id AS store_id ,cat_index.visibility AS visibility ,price_index.customer_group_id AS customer_group_id ,price_index.website_id AS website_id ,`cat_index`.`position` AS `cat_index_position`, price_index.price AS `indexed_price`,  `price_index`.`price`,  `price_index`.`final_price`, `price_index`.`min_price` AS `minimal_price`, `price_index`.`min_price`, `price_index`.`max_price`, `price_index`.`tier_price` FROM `catalog_product_entity` AS `e`
INNER JOIN `catalog_category_product_index` AS `cat_index` 
ON cat_index.product_id=e.entity_id AND cat_index.store_id='1' AND cat_index.visibility IN(1, 2, 4) AND cat_index.category_id='2'
INNER JOIN `catalog_product_index_price` AS `price_index` 
ON price_index.entity_id = e.entity_id AND price_index.website_id = '1' AND price_index.customer_group_id = 0 AND e.updated_at > DATE_SUB(NOW(),INTERVAL 1000 DAY)
ORDER BY `e`.`entity_id` desc  ";
$readPrdQuery = $db->query($productsQuery)->fetchAll();
//$resultPrdcts = mysql_fetch_array($readPrdQuery);
$j = 0;
//while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)
{
	//var_dump($resultPrdcts);exit ;
		$prdId =  $resultPrdcts['entity_id'];
		$_productC = "SELECT * FROM `catalog_category_product_index` WHERE `product_id` = '".$resultPrdcts['entity_id']."'";
		$readCrdQuery = $db->query($_productC)->fetchAll();
			$value = array();
			$k = 0;
			$valueId[0] ='';
			$valueId[1] ='';
			$valueId[2] ='';
			$valueId[3] ='';
			//while($resultCategs = mysql_fetch_array($readCrdQuery)){
			 foreach($readCrdQuery as $resultCategs){
			$valueId[$k++] = $resultCategs['category_id'];
			//$value[] = $category->getName();
			}
		$categoryId1 = $valueId[0];
		$categoryId2 = $valueId[1];
		$categoryId3 = $valueId[2]; 
		$categoryId4 = $valueId[3];
		
		$readQuery = "select `entity_id` from `catalog_product_craftsvilla1` where `entity_id` = '".$prdId."'";
		$results = $db->query($readQuery);
		$id = $results->fetch();
		$prod = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 531";
			$vendorresults = $db->query($prod);
			$vendor = $vendorresults->fetch();
			$obj1 = "custom_vars_combined";
			$vendorData = "SELECT `zip`,`".$obj1."`FROM `udropship_vendor` WHERE `vendor_id` = '".$vendor['value']."'";
			$resultData = $db->query($vendorData);
			$vendor_row = $resultData->fetch(); 
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
		//echo $id[0]; exit;
			if(!$id[0])
			{
				$import = "INSERT INTO `catalog_product_craftsvilla1`(`entity_id`,`entity_type_id`,`attribute_set_id`,`type_id`,`sku`,`created_at`,`updated_at`,`has_options`,`required_options`, `position`, `indexed_price`, `price`, `final_price`, `minimal_price`, `min_price`, `max_price`, `tier_price`, `website_id`, `customer_group_id`, `visibility`, `category_id1`, `category_id2`, `category_id3`,`category_id4`, `store_id`, `cod`, `zip`, `udropship_vendor`) 
														  VALUES ('".$prdId."','".$resultPrdcts['entity_type_id']."','".$resultPrdcts['attribute_set_id']."','".$resultPrdcts['type_id']."','".mysql_real_escape_string($resultPrdcts['sku'])."','".$resultPrdcts['created_at']."','".$resultPrdcts['updated_at']."','".$resultPrdcts['has_options']."','".$resultPrdcts['required_options']."','".$resultPrdcts['cat_index_position']."','".$resultPrdcts['indexed_price']."','".$resultPrdcts['price']."','".$resultPrdcts['final_price']."','".$resultPrdcts['minimal_price']."','".$resultPrdcts['min_price']."','".$resultPrdcts['max_price']."','".$resultPrdcts['tier_price']."','".$resultPrdcts['website_id']."','".$resultPrdcts['customer_group_id']."','".$resultPrdcts['visibility']."','".$categoryId1."','".$categoryId2."','".$categoryId3."','".$categoryId4."','".$resultPrdcts['store_id']."','".$codMthd."','".$zip."','".$vendor['value']."')";
			$db->query($import);
		    }
			else{
				$import = "UPDATE `catalog_product_craftsvilla1` SET `entity_id`='".$prdId."',`entity_type_id` = '".$resultPrdcts['entity_type_id']."',`attribute_set_id` = '".$resultPrdcts['attribute_set_id']."',`type_id` = '".$resultPrdcts['type_id']."',`sku` = '".mysql_real_escape_string($resultPrdcts['sku'])."',`created_at` = '".$resultPrdcts['created_at']."',`updated_at` = '".$resultPrdcts['updated_at']."', `has_options` = '".$resultPrdcts['has_options']."',`required_options` = '".$resultPrdcts['required_options']."',`position`='".$resultPrdcts['cat_index_position']."',`indexed_price`='".$resultPrdcts['indexed_price']."',`price`='".$resultPrdcts['price']."',`final_price`='".$resultPrdcts['final_price']."',`minimal_price`='".$resultPrdcts['minimal_price']."',`min_price`='".$resultPrdcts['min_price']."',`max_price`='".$resultPrdcts['max_price']."',`tier_price`='".$resultPrdcts['tier_price']."',`website_id`='".$resultPrdcts['website_id']."',`customer_group_id`='".$resultPrdcts['customer_group_id']."',`visibility`='".$resultPrdcts['visibility']."',`category_id1`='".$categoryId1."',`category_id2`='".$categoryId2."',`category_id3`='".$categoryId3."',`category_id4`='".$categoryId4."',`store_id`='".$resultPrdcts['store_id']."',`cod`='".$codMthd."',`zip`='".$zip."',`udropship_vendor`='".$vendor['value']."'
				WHERE `catalog_product_craftsvilla1`.`entity_id` ='".$prdId."'";
				$db->query($import);
				}
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds
