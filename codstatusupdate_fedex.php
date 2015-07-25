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


//$baseUrl = Mage::helper('courier')->getWsdlPath();
//SOAP object
//$soapClient = new SoapClient($baseUrl . 'Tracking.wsdl');
//$clientInfo = Mage::helper('courier')->getClientInfo();
$aramex_errors = false;
//echo '<pre>';print_r($soapClient->__getFunctions());

$shipmentDataquery = "SELECT `main_table`.*, `d`.`number`,
`f`.`method` FROM `sales_flat_shipment` AS `main_table`
 LEFT JOIN `sales_flat_shipment_track` AS `d` ON
d.parent_id=main_table.entity_id
 LEFT JOIN `sales_flat_order_payment` AS `f` ON
main_table.order_id=f.parent_id WHERE (main_table.udropship_status IN
(1,24,27,28,30,31,25)  AND d.courier_name ='Fedex' AND d.number IS NOT NULL
AND f.method ='cashondelivery' AND main_table.created_at BETWEEN
DATE_SUB(NOW(),INTERVAL 120 DAY) AND DATE_SUB(NOW(),INTERVAL 3 DAY))
ORDER BY `main_table`.`created_at` DESC";

$rconnection = Mage::getSingleton('core/resource')->getConnection('core_read');
$shipmentreport       = $rconnection->fetchAll($shipmentDataquery);
$rconnection->closeConnection();

echo 'Total Shipments to check'.count($shipmentreport);
$incntr = 0;
$totalDelivered = 0;
$totalReturned = 0;
$totalPicked = 0;
$totalShipped = 0;
foreach($shipmentreport as $_shipmentreport)
{
	if(($incntr < 15000) && ($incntr >= 100))
	{
	//sleep(1);
	$vendoremail = $_shipmentreport['udropship_vendor'];
	$vendor = Mage::getModel('udropship/vendor')->load($vendoremail);
	echo $vendorname = $vendor['vendor_name'];
	$vendor_email = $vendor['email'];
	$custemail = $_shipmentreport['email'];
	echo $incrementid = $_shipmentreport['increment_id'];
	echo 'Tracking Number: ';
	echo $awbNumberDel = trim($_shipmentreport['number']); 
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
echo $response->CompletedTrackDetails->TrackDetails->StatusDetail->Description;
echo $waybillstatus =  $response->CompletedTrackDetails->TrackDetails->StatusDetail->Code; 
echo '\n';
	} catch (Exception $e)
	{
		echo "Exception in Soap occured";
		continue;
	}	
	$sendEmailFlag = false;
		if(($waybillstatus=='DL'))
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
					$body = 'Your COD Shipment '.$incrementid.' has been RTOed by the Logistic Service Provider. Please contact the logistics service provider within 7 days, if you do not receive your shipment back. You can email with your tracking number to dhaval.rao@aramex.com!';	
                        }

                }
		elseif($waybillstatus=='PU')
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
		elseif(($waybillstatus=='DP') || ($waybillstatus=='IX') || ($waybillstatus=='ED') || ($waybillstatus=='IT') || ($waybillstatus=='AF') || ($waybillstatus=='OD') || ($waybillstatus=='FD'))
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
		elseif(($waybillstatus=='RS') || ($waybillstatus=='DE'))
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
	$incntr++;
} // End of for each loop;

				$summary = "Fedex COD Delivered: ".$totalDelivered.", Shipped: ".$totalShipped.", Picked: ".$totalPicked.", Returned: ".$totalReturned;
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
