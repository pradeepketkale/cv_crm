<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
//require_once('/home/amit/doejofinal/lib/fedexc/fedex-common-pickup.php');
require_once(Mage::getBaseDir('lib').'/fedexc/fedex-common-pickup.php');
$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

//$vendorDetails = "SELECT DISTINCT `udropship_vendor` FROM `sales_flat_shipment`as sfs WHERE sfs.`created_at` between  (DATE_SUB(NOW(),INTERVAL 1 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR))";
$vendorDetails = "SELECT  DISTINCT sfs.`udropship_vendor` FROM `sales_flat_shipment` as sfs 
LEFT JOIN `sales_flat_shipment_track` as sfst  ON sfst.`parent_id` = sfs.`entity_id`
LEFT JOIN `udropship_vendor` as uv ON sfs.`udropship_vendor` = uv.`vendor_id`
LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.`order_id` = sfop.`parent_id` 
WHERE sfs.`created_at` between  (DATE_SUB(NOW(),INTERVAL 10 DAY)) AND (DATE_SUB(NOW(),INTERVAL 1 HOUR)) AND sfop.`method` = 'cashondelivery' AND sfst.`courier_name` = 'Fedex'";
$vendortResult = $readcon->query($vendorDetails)->fetchAll();
$k=0;
$numdisable = 0;
echo "Total Pickups:".sizeof($vendortResult); 
foreach($vendortResult as $_vendortResult)
{
	if($k < 1000)
	{	
		$pickupIdFedex = createPickupId($_vendortResult['udropship_vendor']);
		
	$numdisable++;
		$storeId1 = Mage::app()->getStore()->getId();
		$templateId1 = "aramex_pick_up_date_email_seller";
		$sender1 = Array('name'  => 'Craftsvilla','email' => 'places@craftsvilla.com');
		$_email1 = Mage::getModel('core/email_template');
		$vars1 = array('shipmentId'=>$_shipmentId, 'vendorName'=>$vendorName, 'awbnumber'=>$awbNumber,'pickupId'=>$pickupIdFedex);
		//print_r($vars1);exit;
		$_email1->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId1))
			->sendTransactional($templateId1, $sender1, $vendorEmail, '', $vars1, $storeId1);
		echo 'Pickup reference created for vendor - '.$_vendortResult['udropship_vendor'] .' And Pick up Id is- '.$pickupIdFedex."\n";

		$insertPickupdataQuery = "INSERT INTO `aramex_pickup_request_number`(`Reference_Number`, `created_date`, `Vendor_id`,`courier_name`) VALUES ('".$pickupIdFedex."',NOW(),'".$_vendortResult['udropship_vendor']."','fedex')";
		$insertPickupdataResult = $write->query($insertPickupdataQuery);		
		echo 'Record inserted succesfully';		
	}
$k++;
}

	$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
	$subject = "Fedex Pickup Request for Total Vendors : ".$k.", Pick Up Created For :".$numdisable." At Time ".$currentTime;

	$mail = Mage::getModel('core/email');
	$mail->setToName("Manoj");
	$mail->setToEmail("manoj@craftsvilla.com");
	$mail->setBody($subject);
	$mail->setSubject($subject);
	$mail->setFromEmail('places@craftsvilla.com');
	$mail->setFromName("Craftsvilla Places");
	$mail->setType('html');
	$mail->send();

function createPickupId($vendorId){
//echo $vendorId;
$dropship = Mage::getModel('udropship/vendor')->load($vendorId);
//		echo '<pre>';print_r($dropship);exit;
		$vendorStreet = mysql_escape_string($dropship['street']);
		$vendorCity = mysql_escape_string($dropship->getCity());
		$vendorName = mysql_escape_string($dropship->getVendorName());
		$vAttn = mysql_escape_string($dropship->getVendorAttn());
		$vendorPostcode = mysql_escape_string($dropship->getZip());
		$vendorEmail = mysql_escape_string($dropship->getEmail());
		$vendorTelephone = mysql_escape_string($dropship->getTelephone());
		$regionId = mysql_escape_string($dropship->getRegionId());
		$region = Mage::getModel('directory/region')->load($regionId);
		$codeState = mysql_escape_string($region->getCode());
		$regionName = mysql_escape_string($region->getName());
		
		$account=Mage::getStoreConfig('courier/general/account_number');		
		$country_code= 'IN';
		$post = '';
		$country = Mage::getModel('directory/country')->loadByCode($country_code);		

$path_to_wsdl = Mage::getBaseDir()."/app/code/local/Craftsvilla/Fedexcourier/etc/wsdl/fedex/PickupService_v9.wsdl";
ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$request['WebAuthenticationDetail'] = array(
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);

$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
//print_r($request);exit;
$request['TransactionDetail'] = array('CustomerTransactionId' => $vendorId);
$request['Version'] = array(
	'ServiceId' => 'disp', 
	'Major' => 9, 
	'Intermediate' => 0, 
	'Minor' => 0
);
$request['OriginDetail'] = array(
	'PickupLocation' => array(
		'Contact' => array(
			'PersonName' => $vendorName,
          	'CompanyName' => $vendorName,
        	'PhoneNumber' => $vendorTelephone
        ),
      	'Address' => array(
      		'StreetLines' => array($vendorStreet),
          	'City' => $vendorCity,
          	'StateOrProvinceCode' => $codeState,
         	'PostalCode' => $vendorPostcode,
           	'CountryCode' => 'IN')
       	),
   	'PackageLocation' => 'FRONT', // valid values NONE, FRONT, REAR and SIDE
    'BuildingPartCode' => 'SUITE', // valid values APARTMENT, BUILDING, DEPARTMENT, SUITE, FLOOR and ROOM
    'BuildingPartDescription' => '3B',
    'ReadyTimestamp' => getProperty('pickuptimestamp'), // Replace with your ready date time
    'CompanyCloseTime' => '20:00:00'
);

$request['PackageCount'] = '1';
$request['TotalWeight'] = array(
	'Value' => '0.5', 
	'Units' => 'KG' // valid values LB and KG
); 
$request['CarrierCode'] = 'FDXE'; // valid values FDXE-Express, FDXG-Ground, FDXC-Cargo, FXCC-Custom Critical and FXFR-Freight
//$request['OversizePackageCount'] = '1';
$request['CourierRemarks'] = 'Pickup';
//print_r($request);exit;


try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $client->createPickup($request);
//$response->PickupConfirmationNumber;
//$response->Location;
$newpickupid = $response->PickupConfirmationNumber.$response->Location;
//exit;	
//print_r($response);exit;
    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        echo 'Pickup confirmation number is: '.$response -> PickupConfirmationNumber .Newline;
        echo 'Location: '.$response -> Location .Newline;
		$newpickupid = $response->Location.$response->PickupConfirmationNumber;
		return $newpickupid;
		printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
    printSuccess($client, $response);              
}


}



