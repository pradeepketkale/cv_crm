<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$msc=microtime(true);
$productsQuery = "SELECT * from `catalog_product_craftsvilla3` ORDER BY `entity_id` desc";
$readPrdQuery = $db->query($productsQuery)->fetchAll();
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));

$countQuery = "SELECT count(`entity_id`) as countIndex FROM `catalog_product_index_price`";
$rsltCountQuery = $db->query($countQuery);

$_countIndex = $rsltCountQuery->fetch();
echo $_countIndex[0]; 
if ($_countIndex[0] < 100000)
{
echo $message = "Failed:Remove Catalog Craftsvilla3:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
exit;
}
else
{

echo $message = "Successful and Started: Remove Catalog Craftsvilla3:: ".$currentTime;
$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

}
//$resultPrdcts = mysql_fetch_array($readPrdQuery);
$j = 0;
$k = 0;
//while($resultPrdcts = mysql_fetch_array($readPrdQuery))
 foreach($readPrdQuery as $resultPrdcts)
{

	//var_dump($resultPrdcts);exit ;
		$prdId =  $resultPrdcts['entity_id'];
		$_productC = "SELECT `entity_id` FROM `catalog_product_index_price`  WHERE `entity_id` = '".$prdId."'";
		$readCrdQuery = $db->query($_productC);
		
		$readCrdresult = $readCrdQuery->fetch();
		//$readCrdresult[0];exit;
		
		if($readCrdresult[0] == '')
			{
				$delteQuery = "DELETE FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$prdId."'";
				$deleteResult = $db->query($delteQuery)->fetch();
				echo '\n deleted'.$prdId;
				$k++;
			}	
		
			
$j++;		
if(($j % 1000)==0){echo '-'. $j;}
}
echo "\n Total deleted ". $k;
