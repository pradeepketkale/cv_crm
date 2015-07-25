<?php

error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
/*$courierUrl = Mage::getStoreConfig('courier/general/server_url');
-$tokenKey = Mage::getStoreConfig('courier/general/source');
-$savexml = file_get_contents($courierUrl.'waybill/api/fetch/json/?token='.$tokenKey);
-$encode_json_str = json_encode($savexml);
-$encode = str_replace('\"','',$encode_json_str);*/


$db = Mage::getSingleton('core/resource')->getConnection('core_read');


$shipmentnumber = $_GET['shipment'];
$shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
									//->addAttributeToFilter('udropship_status', '24')
									->addAttributeToFilter('increment_id',$shipmentnumber);
//echo '<pre>';print_r($shipmentCollection->getData());exit;
foreach($shipmentCollection as $_shipmentCollection)
{
	$incrementid = $_shipmentCollection->getIncrementId();
	$_shipmentCollection->getUdropshipStatus();
	$_shipmentCollection->getUdropshipVendor();
	$orderid = $_shipmentCollection->getOrderId();
	$entityid = $_shipmentCollection->getEntityId();
	$track = "select `number` from `sales_flat_shipment_track` where `parent_id` = '".$entityid."'";
	$tracknumber = $db->query($track)->fetchAll();
	//while($row = mysql_fetch_array($tracknumber))
	foreach($tracknumber as $row)
	{
		$trackingnumber = $row['number'];
		
	}
	$method = "select `method` from `sales_flat_order_payment` where `parent_id` = '".$orderid."'";
	$result = $db->query($method)->fetchAll();
	//while($row1 = mysql_fetch_array($result))
	foreach($result as $row1)
	{
		$row1['method'];
		if($row1['method']=='cashondelivery')
	{
	$barcodeOptions = array(
                    'text' => $trackingnumber,
					'drawText' => TRUE,
                    'align' => 'left'
                );
			
			
			$rendererOptions = array('imageType' => 'jpg');
			$renderer = Zend_Barcode::render(
				'code25', 'image', $barcodeOptions, $rendererOptions
			);
	}
	}
}
/*$awbno = str_replace('"','',$encode);*/
 //$pdf = new Zend_Pdf(); 
	//$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
	/*	$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),36);
          Zend_Barcode::setBarcodeFont('helvetica.ttf');*/
   
	


?>
