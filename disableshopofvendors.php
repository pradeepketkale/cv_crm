<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');
//$vendorIdquery = "SELECT * FROM `udropship_vendor`";
$vendorIdquery = "SELECT * FROM `udropship_vendor` WHERE `fax` IS NULL OR `fax` = 'unsetvacation' OR `fax` = ''";
$vendorIdResult = $readcon->query($vendorIdquery)->fetchAll();
$k=0;
$bodymessage = '';
$bodymessage2 = '';
$numdisable = 0;
$dryrun = true; 
echo "Total Sellers:".sizeof($vendorIdResult); 
$n = 0;
foreach($vendorIdResult as $_vendorIdResult){
if($k < 15000)
{
	$vendorId = $_vendorIdResult['vendor_id'];
	$vendorEmail = $_vendorIdResult['email'];
echo	$vendorName = $_vendorIdResult['vendor_name'];	
	$templateId = 'disable_shop_email_template1';
	$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
	$_email = Mage::getModel('core/email_template');

$shipmentDetails = "SELECT count(*) AS cntshipment FROM `sales_flat_shipment` as sfs WHERE sfs.`created_at` > (DATE_SUB(NOW(),INTERVAL 180 DAY)) AND `udropship_vendor` = '".$vendorId."'";
$shipmentResult = $readcon->query($shipmentDetails)->fetch();
$countshipment = $shipmentResult['cntshipment']."\n";
//get 5% of shipment
$shipment50per = round($countshipment * 0.5);


$cancelshipmentQuery = "SELECT count(*) AS cntcancelshipment FROM `sales_flat_shipment` as sfs WHERE sfs.`created_at` > (DATE_SUB(NOW(),INTERVAL 60 DAY)) AND sfs.`udropship_status` IN ('6','23','18','12','20') AND `udropship_vendor` = '".$vendorId."'";
$cancelshipmentResult = $readcon->query($cancelshipmentQuery)->fetch();
$countcancel = $cancelshipmentResult['cntcancelshipment']."\n";

$totalper = round(($countcancel/$countshipment)*100);

//$bodymessage .= "Seller ".$vendorName."(".$vendorId.") disabled because of ".$totalper ."% default on total shipment ".$countshipment." ordered last 180 days<br/>" ;

if($countshipment > 5)
{
	if($countcancel >= $shipment50per){
	
		if(!$dryrun)
		{
			if($n < 10)
			{
			$product = Mage::getModel('catalog/product')->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('udropship_vendor',$vendorId);
			$fromPart = $product->getSelect()->getPart(Zend_Db_Select::FROM);
			$fromPart['e']['tableName'] ='catalog_product_entity';
			$product->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
			$j = 0;
			$productIdStr = "";
			echo "Total Products to Out of Stock:".sizeof($product); 
			echo "Vendor Id:".$vendorId; 
			foreach($product as $_productoutofstock)
				 {
					echo $j++;
					echo "Making Out of Stock Id:";
					echo $_productoutofstock['entity_id']."/n"; 
					if($j < 1000)
					{
					$productIdStr .= $_productoutofstock['entity_id']."-";
					$productquery = "update `cataloginventory_stock_item` set `is_in_stock`= 0 WHERE `product_id`=".$_productoutofstock['entity_id'];
					$write->query($productquery);	
					}
				 }
			$seller = "UPDATE `udropship_vendor` SET `fax` = 'vacation' WHERE `vendor_id`='".$vendorId."'";
				 $write->query($seller);
				 
				$vars = Array('vendorName' =>$vendorName,
								'vendorEmail' =>$vendorEmail,
								);
			
				$_email->sendTransactional($templateId,$sender,$vendorEmail,$vendorName,$vars, $storeId);
				$bodymessage2 .= "Seller ".$vendorName."(".$vendorId.") disabled total products ".$j." because of ".$totalper ."% default on total shipment ".$countshipment." ordered last 180 days<br/>" ;
		echo 'Shop '.$vendorName.' Has Been Disabled Successfully.'."/n";
			}
			$n++;
		}	

		$bodymessage .= "Seller ".$vendorName."(".$vendorId.") disabled because of ".$totalper ."% default on total shipment ".$countshipment." ordered last 180 days<br/>" ;
		//echo 'Shop '.$vendorName.' Has Been Disabled Successfully.'."\n";
		$numdisable++;	
	}

	else{
		//echo "not disabled";
	}
}
}
$k++;
}

	$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
	$subject = "Total Sellers to Disable: ".$k.", Sellers Disabled:".$numdisable." At Time ".$currentTime;

	$mail = Mage::getModel('core/email');
	$mail->setToName("Manoj");
	$mail->setToEmail("manoj@craftsvilla.com");
	$mail->setBody($bodymessage.$bodymessage2);
	$mail->setSubject($subject);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Places");
	$mail->setType('html');
	$mail->send();
	$mail->setToEmail("monica@craftsvilla.com");
	$mail->send();
