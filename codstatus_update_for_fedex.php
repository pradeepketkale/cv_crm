<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
ini_set('display_errors', '1');
require_once(Mage::getBaseDir('lib').'/fedexc/fedex-common-track.php');
ini_set('display_errors', '1');
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$baseUrl = Mage::helper('fedexcourier')->getWsdlPath();
$soapClient = new SoapClient($baseUrl . 'TrackService_v9.wsdl');
$aramex_errors = false;
//echo '<pre>';print_r($soapClient->__getFunctions());exit;

$shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
$shipmentData->getSelect()->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id','d.number')
//			->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
                                                                 // ->where('main_table.udropship_status IN (24,27,28) AND d.courier_name ="Aramex"');
			->join(array('f'=>'sales_flat_order_payment'),'main_table.order_id=f.parent_id','f.method')
                        //->where('main_table.udropship_status IN (1,24,27,28)  AND d.courier_name ="Aramex" AND f.method ="cashondelivery" AND main_table.created_at >= DATE_SUB(NOW(),INTERVAL 120 DAY) AND main_table.increment_id = "100097524"')
                        ->where('main_table.udropship_status IN (1,24,27,28,30,31)  AND d.courier_name Like "%Fedex%" AND f.method ="cashondelivery" AND main_table.created_at >= DATE_SUB(NOW(),INTERVAL 120 DAY)')
			->order('main_table.created_at DESC');
//$shipmentData->getSelect()->__toString(); exit;
$shipmentreport = $shipmentData->getData();
'Total Shipments to check'.$shipmentData->count(); 
$incntr = 0;
$totalDelivered = 0;
$totalReturned = 0;
$totalPicked = 0;
$totalShipped = 0;
foreach($shipmentreport as $_shipmentreport)
{

	if(($incntr < 5000) && ($incntr > 1000))
	{
	//sleep(1);
	$vendoremail = $_shipmentreport['udropship_vendor'];
	$vendor = Mage::getModel('udropship/vendor')->load($vendoremail);
	$vendorname = $vendor['vendor_name'];
	$vendor_email = $vendor['email'];
	$custemail = $_shipmentreport['email'];
	echo $incrementid = $_shipmentreport['increment_id'];
	echo 'Tracking Number: ';
	echo $awbNumberDel = trim($_shipmentreport['number']);
	//$awbNumberDel = '772834777432';
	$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
	$shipmentStatus = $shipment->getUdropshipStatus();
		
	//$aramexParams['ClientInfo'] = $clientInfo;
	//$aramexParams['Transaction']    = array('Reference1' => $incrementid );
	//$aramexParams['Shipments']              = array($awbNumberDel);
	//print_r($aramexParams);exit;

	$request['WebAuthenticationDetail'] = array(
	'UserCredential' => array(
		'Key' => 'loM2hp8EMN1WJ06W', 
		'Password' => 'MLbgtBsfYmqARP7YYDf2B8WmL'
	)
);

$request['ClientDetail'] = array(
	'AccountNumber' => '619389786', 
	'MeterNumber' => '107640079'
);
 $request['SelectionDetails'] = array(
	'PackageIdentifier' => array(
		'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
		'Value' => $awbNumberDel // Replace 'XXX' with a valid tracking identifier
	)
);

//print_r($request);exit;
$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'trck', 
	'Major' => '9', 
	'Intermediate' => '1', 
	'Minor' => '0'
);

//print_r($request);exit;
$delstatus='';
try {
	if(setEndpoint('changeEndpoint')){

		$newLocation = $soapClient->__setLocation(setEndpoint('endpoint'));
	}
	
	$response = $soapClient->track($request);
//echo '<pre>';print_r($response);exit;
echo $HAWBHistory = $response->CompletedTrackDetails->TrackDetails->StatusDetail->Description;exit;

} catch (SoapFault $exception) {
  //  printFault($exception, $soapClient);
}
	
	//echo '<pre>';print_r($_resAramex);exit;
	$waybillstatus = '';
	$waybillstatus1 = '';
	$waybillstatus25 = '';
	$waybillstatus25_1 = '';
	
	if(!empty($awbNumberDel))
	{
//		print_r($_resAramex); exit;
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
        	foreach($_resAramex->Notifications as $notification){
                                $errorMessage .= '<b>' . $notification->Code . '</b>' . $notification->Message;
        	}
		continue;
	} 

	$sendEmailFlag = false;
		if(($waybillstatus=='Delivered') || ($waybillstatus=='Shipment delivered') || ($waybillstatus1=='Delivered') || ($waybillstatus1=='Shipment delivered') || ($waybillstatus2=='Delivered') || ($waybillstatus2=='Shipment delivered'))
                {
                        if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED)
                        {
                                //$sendEmailFlag = true;
				$totalDelivered++;
                                $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
                                                        Mage::helper('udropship')->addShipmentComment($shipment,
                                                                          ('Delivered to Customer by Logistic Service Provider'));
                                                $shipment->save();
                                        $subject = 'COD Shipment '.$incrementid.' has been successfully delivered to customer by our Logistic Service Provider!';
                                        //$body = 'Dear Seller,<br><br> Your COD shipment '.$incrementid.' has been successfully delivered to customer by our Logistic Service Provider!';
					$body = 'Your COD Shipment '.$incrementid.' has been RTOed by the Logistic Service Provider. Please contact the logistics service provider within 7 days, if you do not receive your shipment back. You can email with your tracking number to india@fedex.com or call to 1800-4194343!';	
                        }

                }
		elseif($waybillstatus=='Received at Operatio')
		{
			if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP)
			{
				//$sendEmailFlag = true;
				$totalPicked++;
                                $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP);
                                Mage::helper('udropship')->addShipmentComment($shipment,
                                                      ('Shipment Picked Up by Logistic Service Provider'));
                                $shipment->save();

                       		$subject = 'COD Shipment '.$incrementid.' Has Been Picked From You By Our Logistic Service Provider!';
               			$body = 'Dear Seller,<br><br> This is to inform you that according to our records your COD shipment '.$incrementid.' has been collected from you by our Logistic Service Provider!';
			}
		}
		elseif(($waybillstatus=='Out for Delivery') || ($waybillstatus=='Departed Operations ') || ($waybillstatus=='Attempted Delivery -'))
		{
			if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED)
			{
				$subject = 'COD Shipment '.$incrementid.' Is On Its Way To Customer By Our Logistic Service Provider!';
                       		$body = 'Dear Seller,<br><br> This is to inform you that according to our records your COD shipment '.$incrementid.' is being attempted for delivery to customer by our Logistic Service Provider!';
				//$sendEmailFlag = true;
				$totalShipped++;
                       		$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
                                Mage::helper('udropship')->addShipmentComment($shipment,
                                                                         ('Shipped to Customer by Logistic Service Provider'));
                               	$shipment->save();
                               	$shipment->sendEmail(true, '')
                                         ->setEmailSent(true)
                                         ->save();
			}
		}
		elseif(($waybillstatus=='To be Returned to Sh') || ($waybillstatus25=='Supporting Document Retur') || ($waybillstatus25_1=='Supporting Document Retur'))
		{
			if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_RTO)
                        {
				$sendEmailFlag = true;
				$totalReturned++;
				 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_RTO);
							Mage::helper('udropship')->addShipmentComment($shipment,
									  ('Returned by Customer to Logistic Service Provider'));
				$shipment->save();
				$body = 'Dear Seller,<br><br>Your COD Shipment '.$incrementid.' has been RTOed by Logistic Service Provider. Please contact the logistics service provider at dhaval.rao@aramex.com, if you do not receive your shipment back within one week.';
				$subject = 'COD Shipment '.$incrementid.' has been RTOed by Logistic Service Provider!';
			}
									
		}
		elseif($waybillstatus=='RTO')
		{
			if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED_TO_SELLER)
                        {
				$sendEmailFlag = true;
				$totalReturned++;
				 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED_TO_SELLER);
							Mage::helper('udropship')->addShipmentComment($shipment,
									 ('Returned to Seller by Logistic Service Provider'));
				$shipment->save();
				$body = 'Dear Seller,<br><br> Your COD shipment '.$incrementid.' is being returned to you by our Logistic Service Provider. Please contact the logistics service provider within 7 days, if you do not receive your shipment back.';
				$subject = 'COD Shipment '.$incrementid.' is being returned to you by our Logistic Service Provider!';
			}
					
		}
		if($sendEmailFlag)
		{
				echo 'Sending Email';
 				$mail = Mage::getModel('core/email');
                                $mail->setToName($vendorname);
                                $mail->setToEmail($vendor_email);
                                $mail->setBody($body);
                                $mail->setSubject($subject);
                                $mail->setFromEmail('places@craftsvilla.com');
                                $mail->setFromName("Craftsvilla Places");
                                $mail->setType('html');
				
				try{
                                	if($mail->send())
                                	{
                                                echo 'Email has been send successfully';
                                        	$mail->setToEmail('manoj@craftsvilla.com');
                                        	$mail->send();
                                                echo 'Email to Manoj has been send successfully';
					}
				} catch (Exception $e) {
           				echo $e->getMessage();
        			}
		}
	}	
	echo $incntr++;
	echo "\n";
} // End of for each loop;

				$summary = "COD Delivered: ".$totalDelivered.", Shipped: ".$totalShipped.", Picked: ".$totalPicked.", Returned: ".$totalReturned;
 				echo 'Sending Summmary Email';
                                $mail = Mage::getModel('core/email');
                                $mail->setToName('Manoj');
                                $mail->setToEmail('manoj@craftsvilla.com');
                                $mail->setBody($summary);
                                $mail->setSubject($summary);
                                $mail->setFromEmail('places@craftsvilla.com');
                                $mail->setFromName("Craftsvilla Places");
                                $mail->setType('html');

                                try{
                                        if($mail->send())
                                        {
                                                echo 'Summary Email has been send successfully';
                                                $mail->setToEmail('monica@craftsvilla.com');
                                                $mail->send();
                                                echo 'Summary Email to Monica has been send successfully';
                                        }
                                } catch (Exception $e) {
                                        echo $e->getMessage();
                                }

?>
