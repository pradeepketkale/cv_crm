<?php
require_once 'app/Mage.php';
Mage::app();
$hlp = Mage::helper('generalcheck');
$mainconn = $hlp->getMaindbconnection();

$catalog_product_Query = mysql_query("SELECT * FROM `catalog_product_craftsvilla3`",$mainconn);

mysql_close($mainconn);

echo "Total products:".mysql_num_rows($catalog_product_Query); 

$statsconn = $hlp->getStatsdbconnection();
$k = 0;
while($catalog_product_result = mysql_fetch_array($catalog_product_Query)){
$k++;
if($k < 2000000)
{
$entity_id = $catalog_product_result['entity_id'];

$catalog_product_QueryStats = mysql_query("SELECT `entity_id` FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$entity_id."'",$statsconn);

$catalog_product_resultStats = mysql_fetch_array($catalog_product_QueryStats);

	if(!$catalog_product_resultStats){

		$catalog_product_insert = "INSERT INTO `catalog_product_craftsvilla3` (`entity_id`, `entity_type_id`, `attribute_set_id`, `type_id`, `sku`, `created_at`, `updated_at`, `has_options`, `required_options`, `price`, `minimal_price`, `min_price`, `max_price`, `tier_price`, `discount`, `shippingtime`, `website_id`, `customer_group_id`, `visibility`, `category_id1`, `category_id2`, `category_id3`, `category_id4`, `store_id`, `cod`, `zip`, `udropship_vendor`, `color`) VALUES ('".$catalog_product_result['entity_id']."','".$catalog_product_result['entity_type_id']."','".$catalog_product_result['attribute_set_id']."','".$catalog_product_result['type_id']."','".mysql_real_escape_string($catalog_product_result['sku'])."','".$catalog_product_result['created_at']."','".$catalog_product_result['updated_at']."','".$catalog_product_result['has_options']."','".$catalog_product_result['required_options']."','".$catalog_product_result['price']."','".$catalog_product_result['minimal_price']."','".$catalog_product_result['min_price']."','".$catalog_product_result['max_price']."','".$catalog_product_result['tier_price']."','".$catalog_product_result['discount']."','".$catalog_product_result['shippingtime']."','".$catalog_product_result['website_id']."','".$catalog_product_result['customer_group_id']."','".$catalog_product_result['visibility']."','".$catalog_product_result['category_id1']."','".$catalog_product_result['category_id2']."','".$catalog_product_result['category_id3']."','".$catalog_product_result['category_id4']."','".$catalog_product_result['store_id']."','".$catalog_product_result['cod']."','".$catalog_product_result['zip']."','".$catalog_product_result['udropship_vendor']."','".$catalog_product_result['color']."')";
		
	mysql_query($catalog_product_insert,$statsconn) or die(mysql_error());
	}
	else
	{

		$catalog_product_Update = "UPDATE `catalog_product_craftsvilla3` SET `entity_id` = '".$catalog_product_result['entity_id']."',`entity_type_id` = '".$catalog_product_result['entity_type_id']."',`attribute_set_id` = '".$catalog_product_result['attribute_set_id']."' ,`type_id` = '".$catalog_product_result['type_id']."' ,`sku`='".mysql_real_escape_string($catalog_product_result['sku'])."',`created_at` = '".$catalog_product_result['created_at']."' ,`updated_at` = '".$catalog_product_result['updated_at']."',`has_options` = '".$catalog_product_result['has_options']."',`required_options` = '".$catalog_product_result['required_options']."',`price` = '".$catalog_product_result['price']."',`minimal_price`='".$catalog_product_result['minimal_price']."',`min_price` = '".$catalog_product_result['min_price']."' ,`max_price` = '".$catalog_product_result['max_price']."',`tier_price` = '".$catalog_product_result['tier_price']."',
`discount` = '".$catalog_product_result['discount']."' ,`shippingtime` = '".$catalog_product_result['shippingtime']."',`website_id`='".$catalog_product_result['website_id']."',`customer_group_id` = '".$catalog_product_result['customer_group_id']."',`visibility` = '".$catalog_product_result['visibility']."',`category_id1` = '".$catalog_product_result['category_id1']."',`category_id2` = '".$catalog_product_result['category_id2']."',`category_id3`='".$catalog_product_result['category_id3']."',`category_id4` = '".$catalog_product_result['category_id4']."',`store_id` = '".$catalog_product_result['store_id']."',`cod` = '".$catalog_product_result['cod']."',`zip` = '".$catalog_product_result['zip']."',`udropship_vendor` = '".$catalog_product_result['udropship_vendor']."',`color` = '".$catalog_product_result['color']."' WHERE `entity_id` = '".$entity_id."' ";
		
	mysql_query($catalog_product_Update,$statsconn) or die(mysql_error());

	}

}
if(($k%100) == 0) echo $k.":";
}
mysql_close($statsconn);

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody("Script Number CVSCRCH002 name stats_product_craftsvilla3_scr.php finished");
$mail->setSubject("Craftsvilla3 Copy Script Finished at Time:".$currentTime);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

?>

