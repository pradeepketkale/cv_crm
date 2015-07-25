<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$productDtl = "SELECT `e`.`entity_id` FROM `catalog_product_entity` AS `e` WHERE (e.created_at > DATE_SUB(NOW() , INTERVAL 12 DAY)) ORDER BY `e`.`entity_id` DESC";

//$result = mysql_query($productDtl);
$result = $db->query($productDtl)->fetchAll();
echo 'num_rows: '.mysql_num_rows($result);
$fetchQuery= $result->fetch();
$i = 0;
//while($fetchQuery = mysql_fetch_array($result)){
 foreach($result as $fetchQuery)
$i++;
if ($i < 12000)
{
$product = Mage::getModel('catalog/product')->load($fetchQuery['entity_id']);
//sleep(5);
//$url ='http://175.41.147.59/catalog/product/view/id/'.$fetchQuery['entity_id']; 
//$url ='http://www.craftsvilla.com/catalog/product/view/id/'.$fetchQuery['entity_id']; 
echo $url =$product->getProductUrl();

$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 0); 
		curl_setopt($ch,CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt( $ch, CURLOPT_POST, 1);
		//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		if(curl_errno($ch))
			{		
				print curl_error($ch);
			}
		curl_close($ch);	
		echo $i.'>Executed URL:'.$url.' ';
}
unset($product);
}
mysql_close();
?>
