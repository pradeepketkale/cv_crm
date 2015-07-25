<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

//$vendorDetails = "SELECT DISTINCT `udropship_vendor` FROM `sales_flat_shipment`as sfs WHERE sfs.`created_at` between  (DATE_SUB(NOW(),INTERVAL 1 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR))";
//$vendorDetails = "SELECT DISTINCT `udropship_vendor` FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`created_at` between  (DATE_SUB(NOW(),INTERVAL 5 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR)) AND sfop.`method` = 'cashondelivery'";
$vendorDetails = "SELECT DISTINCT `udropship_vendor` FROM `sales_flat_shipment` as sfs 
LEFT JOIN `sales_flat_shipment_track` as sfst  ON sfst.`parent_id` = sfs.`entity_id`
LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` WHERE sfs.`updated_at` between  (DATE_SUB(NOW(),INTERVAL 5 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR)) AND sfop.`method` = 'cashondelivery' AND sfst.`courier_name` = 'Aramex'";
$vendortResult = $readcon->query($vendorDetails)->fetchAll(); 
echo "Total Vendors to Pick:".sizeof($vendortResult); 
$k=0;
$numdisable = 0;
foreach($vendortResult as $_vendortResult)
{
	if($k < 2000)
	{	
		$dropship = Mage::getModel('udropship/vendor')->load($_vendortResult['udropship_vendor']);
		//print_r($dropship);exit;
		$vendorStreet = $dropship['street'];
		$vendorCity = $dropship->getCity();
		$vendorName = $dropship->getVendorName();
		$vAttn = $dropship->getVendorAttn();
		$vendorPostcode = $dropship->getZip();
		$vendorEmail = $dropship->getEmail();
		$vendorTelephone = $dropship->getTelephone();
		$regionId = $dropship->getRegionId();
		$region = Mage::getModel('directory/region')->load($regionId);
		$regionName = $region->getName();
		
		$account=Mage::getStoreConfig('courier/general/account_number');		
		$country_code= 'IN';
		$post = '';
		$country = Mage::getModel('directory/country')->loadByCode($country_code);		
		$response=array();
		$clientInfo = Mage::helper('courier')->getClientInfo();
		try {
//		echo $pickupDate = $pickupdateAramex;		
		$pickupDate = time() + (1 * 24 * 60 * 60);		
		$readyTimeH=15;
		$readyTimeM=10;			
		$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
		$closingTimeH=18;
		$closingTimeM=59;
		$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		$params = array(
		'ClientInfo'  	=> $clientInfo,
								
		'Transaction' 	=> array(
								'Reference1'			=> $vendorName 
								),
								
		'Pickup'		=>array(
								'PickupContact'			=>array(
									'PersonName'		=>html_entity_decode(substr($vAttn.','.$vendorName,0,45)),
									'CompanyName'		=>html_entity_decode($vendorName),
									'PhoneNumber1'		=>html_entity_decode($vendorTelephone),
									'PhoneNumber1Ext'	=>html_entity_decode(''),
									'CellPhone'			=>html_entity_decode($vendorTelephone),
									'EmailAddress'		=>html_entity_decode($vendorEmail)
								),
								'PickupAddress'			=>array(
									'Line1'				=>html_entity_decode($vendorStreet),
									'City'				=>'',//html_entity_decode($vendorCity),
									'StateOrProvinceCode'=>'',//html_entity_decode($regionName),
									'PostCode'			=>html_entity_decode($vendorPostcode),
									'CountryCode'		=>'IN'
								),
								
								'PickupLocation'		=>html_entity_decode('Reception'),
								'PickupDate'			=>$readyTime,
								'ReadyTime'				=>$readyTime,
								'LastPickupTime'		=>$closingTime,
								'ClosingTime'			=>$closingTime,
								'Comments'				=>html_entity_decode('Please Pick up'),
								'Reference1'			=>html_entity_decode($vendorName),
								'Reference2'			=>'',
								'Vehicle'				=>'',
								'Shipments'				=>array(
									'Shipment'					=>array()
								),
								'PickupItems'			=>array(
									'PickupItemDetail'=>array(
										'ProductGroup'	=>'DOM',
										'ProductType'	=>'CDA',
										'Payment'		=>'3',										
										'NumberOfShipments'=>'1',
										'NumberOfPieces'=>'',										
										'ShipmentWeight'=>array('Value'=>'0.5','Unit'=>'KG'),
										
									),
								),
								'Status' =>'Ready'
							)
	);
	$baseUrl = Mage::helper('courier')->getWsdlPath();
	//$soapClient = new SoapClient($baseUrl . 'shipping.wsdl');
	$soapClient = new SoapClient($baseUrl . 'shipping-services-api-wsdl.wsdl');
	try{
	$results = $soapClient->CreatePickup($params);		
//	echo '<pre>';print_r($results);exit;
	if($results->HasErrors){
		if(count($results->Notifications->Notification) > 1){
			$error="";
			foreach($results->Notifications->Notification as $notify_error){
				//$error.=$this->__('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message)."<br>";				
				$error.='Aramex: ' . $notify_error->Code .' - '. $notify_error->Message."<br>";				
				}
				$response['error']=$error;
			}else{
				//$response['error']=$this->__('Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message);
				$response['error']='Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message;
			}
			$response['type']='error';
		}else{
			
			 $getPickupRequestId = $results->ProcessedPickup->ID;
			//email to seller
			$storeId1 = Mage::app()->getStore()->getId();
			$templateId1 = "aramex_pick_up_date_email_seller";
			$sender1 = Array('name'  => 'Craftsvilla','email' => 'places@craftsvilla.com');
			$_email1 = Mage::getModel('core/email_template');
			$vars1 = array('shipmentId'=>$_shipmentId, 'vendorName'=>$vendorName, 'awbnumber'=>$awbNumber,'pickupId'=>$getPickupRequestId);
			//print_r($vars1);exit;
			$_email1->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId1))
				->sendTransactional($templateId1, $sender1, $vendorEmail, '', $vars1, $storeId1);
			echo 'Pickup reference created for vendor - '.$_vendortResult['udropship_vendor'] .' And Pick up Id is- '.$getPickupRequestId."\n";
	$insertPickupdataQuery = "INSERT INTO `aramex_pickup_request_number`(`Reference_Number`, `created_date`, `Vendor_id`) VALUES ('".$getPickupRequestId."',NOW(),'".$_vendortResult['udropship_vendor']."')";
			$insertPickupdataResult = $write->query($insertPickupdataQuery);		
			echo 'Record inserted succesfully';
			}
		} catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
			}
		}
		catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
		}
	json_encode($response);
	$numdisable++;		
	}
$k++;
}

	$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
	$subject = "Total Aramex vendors : ".$k.", Total Pickups Requested:".$numdisable." At Time ".$currentTime;

	$mail = Mage::getModel('core/email');
	$mail->setToName("Manoj");
	$mail->setToEmail("manoj@craftsvilla.com");
	$mail->setBody($subject);
	$mail->setSubject($subject);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Seller Logistics");
	$mail->setType('html');
	$mail->send();

