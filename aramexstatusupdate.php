<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');
require_once 'app/Mage.php';
Mage::app();

//$shipmentDetails =  



$baseUrl = Mage::helper('courier')->getWsdlPath();
//SOAP object
$soapClient = new SoapClient($baseUrl . 'Tracking.wsdl');
$clientInfo = Mage::helper('courier')->getClientInfo();	
$aramex_errors = false;
//echo '<pre>';print_r($soapClient->__getFunctions());
$aramexParams['ClientInfo'] = $clientInfo;
$aramexParams['Transaction'] 	= array('Reference1' => '100093556' );
$aramexParams['Shipments'] 		= array('40099739326');
//print_r($aramexParams);exit;
$_resAramex = $soapClient->TrackShipments($aramexParams);
//echo '<pre>';print_r($_resAramex);exit;
if(is_object($_resAramex) && !$_resAramex->HasErrors)
	{
	$HAWBHistory = $_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
		foreach($HAWBHistory as $HAWBUpdate)
		{
			$statusAramex = $HAWBUpdate->UpdateDescription;break;
		
		}
	} 
else
	{
	$errorMessage = '';
	foreach($_resAramex->Notifications as $notification){
				$errorMessage .= '<b>' . $notification->Code . '</b>' . $notification->Message;
		}
				
		}
    