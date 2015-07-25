<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$msc=microtime(true);
$productsQuery = "SELECT * From `catalog_product_craftsvilla1` ORDER BY `entity_id` DESC";
$readPrdQuery = $db->query($productsQuery)->fetchAll();
$j = 0;
//while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)
{
//	var_dump($resultPrdcts); 
		$prdId =  $resultPrdcts['entity_id'];
		$sku = $resultPrdcts['sku'];
		$readQuery = "select * from `catalog_product_craftsvilla1` where `entity_id` = '".$prdId."'";
		$results = $db->query($readQuery);
		$id = $results->fetch();
		//echo $id[0]; exit;
			if($id[0])
			{
			//$prod = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
			//$vendorId = $prod->getUdropshipVendor();
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
			$import = "UPDATE `catalog_product_craftsvilla1` SET `cod` = '".$codMthd."',`zip` = '".$zip."',`vendor` = '".$vendor['value']."' WHERE `catalog_product_craftsvilla1`.`entity_id` ='".$prdId."'";
			$db->query($import) or die(mysql_error());
			}
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
$msc=microtime(true)-$msc;
echo $msc.' seconds'; // in seconds
