<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$productDtl = "SELECT `e`.`entity_id` FROM `catalog_product_entity` AS `e` WHERE (e.created_at > DATE_SUB(NOW() , INTERVAL 1 DAY)) ORDER BY `e`.`entity_id` DESC";
//$productDtl = "SELECT `e`.`entity_id` FROM `catalog_product_entity` AS `e` WHERE (e.created_at BETWEEN DATE_SUB(NOW() , INTERVAL 12 DAY) AND DATE_SUB(NOW() , INTERVAL 11 DAY)) ORDER BY `e`.`entity_id` DESC";


$result = $db->query($productDtl)->fetchAll();
echo 'num_rows: '.mysql_num_rows($result); 
$fetchQuery=$result->fetch();
$i = 0;
//while($fetchQuery = mysql_fetch_array($result)){
 foreach($result as $fetchQuery){
	$i++;
	//if (($i < 600) && ($i > 400))
	if ($i < 5000)
	{
		$entityid = $fetchQuery['entity_id'];
		//$entityid = 342341;
		$product = Mage::getModel('catalog/product')->load($entityid);
		$imageUrl166 = Mage::helper('catalog/image')->init($product,'small_image')->resize(166,166);
		//$imageUrl500 = Mage::helper('catalog/image')->init($product,'image')->resize(500,500);
		echo $i.'>Executed URL:'.$imageUrl166.' ';
		//echo "\n"; 
		//echo $i.'>Executed URL:'.$imageUrl500.' ';
		echo "\n"; 
		unset($product);
	}
}
mysql_close();
?>
