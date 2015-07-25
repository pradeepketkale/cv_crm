<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$msc=microtime(true);
$productsQuery = "SELECT * FROM `catalog_product_craftsvilla3` ORDER BY `entity_id` ASC LIMIT  10000  ";
$readPrdQuery = $db->query($productsQuery)->fetchAll();
//$resultPrdcts = mysql_fetch_array($readPrdQuery);
$j = 0;

//while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)
{
	//var_dump($resultPrdcts);exit ;
		$prdId =  $resultPrdcts['entity_id'];

//for getting product url * image url
	$productModelLoad = Mage::getModel('catalog/product')->load($prdId);
	$categoryProductModelUrl1 = $productModelLoad->getProductUrl();
	$categoryProductModelUrl = substr(parse_url($categoryProductModelUrl1, PHP_URL_PATH),1);

	$catalogImageca = Mage::helper('catalog/image')->init($productModelLoad, 'small_image')->resize(166, 166);

	$readQuery = "select `entity_id` from `catalog_list_craftsvilla_s1` where `entity_id` = '".$prdId."'";
	$results = $db->query($readQuery);
	$id = $results->fetch();
	$prod = "SELECT * FROM `catalog_product_entity_int` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 531";
	$vendorresults = $db->query($prod);
	$vendor = $vendorresults->fetch();
			
			//for get url_key & vendor name
			$vendoListData2145 = "SELECT * FROM `udropship_vendor` WHERE `vendor_id` = '".$vendor['value']."'";
			$vendoListData2145results = $db->query($vendoListData2145);
			$vendorList2313 = $vendoListData2145results->fetch();

			$orginalPrice = "SELECT * FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 60";
			$orginalPriceresults = $db->query($orginalPrice);
			$exactPrice = $orginalPriceresults->fetch();
			$setexactPrice = $exactPrice['value'];
			$specialPrice = "SELECT * FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 61";
			$specialPriceresults = $db->query($specialPrice);
			$specialExactPrice = $specialPriceresults->fetch();
			$setspecialPrice = $specialExactPrice['value'];
			if((!empty($setspecialPrice)) && ($setspecialPrice > 0)){ $set12specialPrice = $setspecialPrice;} else{ $set12specialPrice = NULL; }
//get the name & description of product
		$productName = "SELECT * FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$prdId."' AND `attribute_id` = 56";
		$productnameresults = $db->query($productName);
		$productnameresultsdata = $productnameresults->fetch();
		$_productName = mysql_escape_string($productnameresultsdata['value']);
			if(!$id[0])
			{
			//echo "Inserting";
			$import = "INSERT INTO `catalog_list_craftsvilla_s1`(`entity_id`, `name`, `price`, `special_price`, `vendor_name`, `Vendor_url`, `product_url`, `image_url`) VALUES ('".$prdId."','".$_productName."','".$setexactPrice."','".$set12specialPrice."','".mysql_escape_string($vendorList2313['vendor_name'])."','".$vendorList2313['url_key']."','".$categoryProductModelUrl."','".$catalogImageca."')";										  	$db->query($import)->fetch();
		    }
			else{
			//echo "Updating";
		$import = "UPDATE `catalog_list_craftsvilla_s1` SET `entity_id`='".$prdId."',`name`='".$_productName."',`price`='".$setexactPrice."',`special_price`='".$set12specialPrice."',`vendor_name`='".mysql_escape_string($vendorList2313['vendor_name'])."',`Vendor_url`='".$vendorList2313['url_key']."',`product_url`='".$categoryProductModelUrl."',`image_url`='".$catalogImageca."' WHERE `entity_id` = '".$prdId."'";
				$db->query($import)->fetch();
				}
$j++;		
if(($j % 100)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds

