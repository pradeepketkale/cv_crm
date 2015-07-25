<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$mail = Mage::getModel('core/email');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
 
$codQuery = "SELECT sfs.order_id, sfs.entity_id, sfs.increment_id, sfs.updated_at, sfop.parent_id, sfst.number,sfst.parent_id,sfst.courier_name FROM `sales_flat_shipment` as sfs LEFT JOIN `sales_flat_order_payment` as sfop ON sfs.order_id = sfop.parent_id  LEFT JOIN `sales_flat_shipment_track` as sfst ON sfs.entity_id = sfst.parent_id WHERE sfs.udropship_status = 20 AND sfop.method = 'cashondelivery' AND sfst.number IS NOT NULL AND sfs.updated_at < DATE_SUB(NOW(),INTERVAL 30 DAY) ORDER BY sfs.entity_id ASC"; 
$codQueryResult = $readcon->query($codQuery)->fetchAll();
//print_r($codQueryResult); exit;
$countAramex = 0;
$countFedex = 0;
echo $emailBody = 'Total Disputed COD Shipments > 30 Days:'.sizeof($codQueryResult).'<br>'; 
$k = 0;
foreach($codQueryResult as $_codQueryResult)
{
     if($k < 500)
     {
		 $parentId = $_codQueryResult['parent_id'];
		 $couriername = $_codQueryResult['courier_name'];
		 echo $incrementid = $_codQueryResult['increment_id']; 
		 $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
		 $fedexDeliverd = 0;
		 $aramexDelivered = 0;
		if($couriername == 'Aramex' || $couriername == 'aramex')
		{
			echo 'Aramex'; 
			$aramexDelivered = isCheckShipmentDeliveredAramex($incrementid);
			if($aramexDelivered == 1) {
				$countAramex++;
				echo "Aramex Delivered"; 
				$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
				Mage::helper('udropship')->addShipmentComment($shipment,('Dispute Raised removed and changed to Delivered as verified by API by System Script.'));
				$shipment->save();	
				$emailBody .= ">Aramex Shipment Converted from Dispute Raised to Delivered:".$incrementid."<br>";
			}
		}
		elseif($couriername == 'Fedex' || $couriername == 'fedex') 
		{
			echo 'Fedex'; 
			$fedexDeliverd = isCheckShipmentDeliveredFedex($incrementid);
			if($fedexDeliverd == 1) {
				echo "Fedex Delivered"; 
				$countFedex++;
			
				$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
				Mage::helper('udropship')->addShipmentComment($shipment,('Dispute Raised removed and changed to Delivered as verified by API by System Script.'));
				$shipment->save();	
				$emailBody .= ">Fedex Shipment Converted from Dispute Raised to Delivered:".$incrementid."<br>";
			}
		}
		
	}
	$k++;
	 
}

$emailBody1 = "Total Aramex Converted:".$countAramex."<br>"."Total Fedex Converted:".$countFedex."<br>";

$totalCount = $countAramex + $countFedex;


					$mail = Mage::getModel('core/email');
					$mail->setToName('Craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($emailBody1.$emailBody);
					$mail->setSubject('Total '.$totalCount.' COD Shipments converted from Dispute Raised');
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName('Craftsvilla Places');
					$mail->setType('html');
					if($mail->send())
					{
						$mail->setToEmail('monica@craftsvilla.com');
						$mail->send();
					echo 'Email has been send successfully';
					}
					else 
					echo "error";



function isCheckShipmentDeliveredAramex($incrementid) 
{
		
		$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
		ini_set('display_errors', '1');
		$baseUrl = Mage::helper('courier')->getWsdlPath();
		$soapClient = new SoapClient($baseUrl . 'Tracking.wsdl');
		$clientInfo = Mage::helper('courier')->getClientInfo();
		$aramex_errors = false;

		$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
		$entityId = $shipment->getEntityId(); 
	
		$trackQuery = "SELECT * FROM `sales_flat_shipment_track` WHERE `parent_id` = '".$entityId."'";
		$trackQueryResult = $readcon->query($trackQuery)->fetch();
		$awbNumber = $trackQueryResult['number'];
		//$awbNumber = 2081427633;
		
		$aramexParams['ClientInfo'] = $clientInfo;
		$aramexParams['Transaction']    = array('Reference1' => $incrementid );
		$aramexParams['Shipments']           = array($awbNumber);
		try {
				if(!empty($awbNumber)) 
				$_resAramex = $soapClient->TrackShipments($aramexParams);
					//echo '<pre>';print_r($_resAramex);exit;
			} 
		catch (Exception $e)
		{
			echo "Exception in Soap occured";
		}
			
		$waybillstatus = '';
		$waybillstatus1 = '';
		$waybillstatus25 = '';
		$waybillstatus25_1 = '';
	
	if(is_object($_resAramex) && !$_resAramex->HasErrorsi && !empty($awbNumber))
	{
		$HAWBHistory = $_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
		$nn = 0;
        	foreach($HAWBHistory as $HAWBUpdate)
        	{
	
				if($nn == 0)
				{
		                		echo $waybillstatus = substr($HAWBUpdate->UpdateDescription,0,20);
		                		echo $waybillstatus25 = substr($HAWBUpdate->UpdateDescription,0,25);
		        }
				elseif($nn == 1)
				{
								echo $waybillstatus1 = substr($HAWBUpdate->UpdateDescription,0,20);
		                        echo $waybillstatus25_1 = substr($HAWBUpdate->UpdateDescription,0,25);
		                        echo ":\n";
		        }
				else
				{
		                	echo $waybillstatus2 = substr($HAWBUpdate->UpdateDescription,0,20);
		                	echo $waybillstatus25_2 = substr($HAWBUpdate->UpdateDescription,0,25);
					echo ":\n";
					break;
			
				}
				$nn++;
           }
	} 
	else
	{
			$errorMessage = '';
				foreach($_resAramex->Notifications as $notification)
				{
			        $errorMessage .= '<b>' . $notification->Code . '</b>' . $notification->Message;
				}
	}
	
if(($waybillstatus=='Delivered') || ($waybillstatus=='Shipment delivered') || ($waybillstatus1=='Delivered') || ($waybillstatus1=='Shipment delivered') || ($waybillstatus2=='Delivered') || ($waybillstatus2=='Shipment delivered'))
                { 
                      return 1;
                }
                else 
                {
                	  return 0;
                }
	
		return 0;	
}		
					
function isCheckShipmentDeliveredFedex($incrementid) 
{
	
	require_once(Mage::getBaseDir('lib').'/fedexc/fedex-common-track.php');
	$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
	$path_to_wsdl = Mage::getBaseDir()."/app/code/local/Craftsvilla/Fedexcourier/etc/wsdl/fedex/TrackService_v9.wsdl";
	ini_set("soap.wsdl_cache_enabled", "0");
	$soapClientFedex = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more  				information
	$clientInfoFedex = Mage::helper('fedexcourier')->getClientInfo();
	$fedex_errors = false;
	//echo '<pre>';print_r($soapClientFedex->__getFunctions()); exit;
	
	$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
    $entityId = $shipment->getEntityId(); 
	
	$trackQuery = "SELECT * FROM `sales_flat_shipment_track` WHERE `parent_id` = '".$entityId."'";
	$trackQueryResult = $readcon->query($trackQuery)->fetch();
	$awbNumber = $trackQueryResult['number'];
	//$awbNumber = 772834777432;
	
	$request['WebAuthenticationDetail'] = array(
	'UserCredential' =>array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
	);
	$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
	);
	$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
	$request['Version'] = array(
	'ServiceId' => 'trck', 
	'Major' => '9', 
	'Intermediate' => '1', 
	'Minor' => '0'
	);
	$request['SelectionDetails'] = array(
	'PackageIdentifier' => array(
		'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
		'Value' => $awbNumber
	)
	);
//print_r($request); exit;
try {
	
		if(setEndpoint('changeEndpoint'))
		{
			$newLocation = $client->__setLocation(setEndpoint('endpoint'));
		}
			$response = $soapClientFedex->track($request);
			//print_r($response); exit;
			$showtable = '';
		if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR')
		{
				if($response->HighestSeverity != 'SUCCESS'){
					$showtable .= '<table border="1">';
					$showtable .='<tr><th>Track Reply</th><th>&nbsp;</th></tr>';
					trackDetails($response->Notifications, '');
					 //echo '</table>';
				}else{
					if ($response->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
						$showtable .= '<table border="1">';
						$showtable .= '<tr><th>Shipment Level Tracking Details</th><th>&nbsp;</th></tr>';
						trackDetails($response->CompletedTrackDetails, '');
					//	echo '</table>';
				}else{
						$showtable .= '<table border="1">';
					    $showtable .= '<tr><th>Package Level Tracking Details</th><th>&nbsp;</th></tr>';
						trackDetails($response->CompletedTrackDetails->TrackDetails, '');
					//	echo'</table>';
					}
				}
		
				$trackStatus = $response->CompletedTrackDetails->TrackDetails->StatusDetail->Code;
				if($trackStatus == 'DL') 
				{
					return 1;
				
				} else 
				{
					return 0;
				}
     
    	}
    	else
    	{
        	printError($client, $response);
    	} 
    
    } 
    catch (SoapFault $exception) 
    {
    	printFault($exception, $client);
	}
	
	return 0;
	 
}				
					
					
?>
