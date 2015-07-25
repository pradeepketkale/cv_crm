<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

		$shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
		$shipmentData->getSelect()->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
								  ->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id','d.number')
								  ->join(array('f'=>'sales_flat_order_payment'),'main_table.order_id=f.parent_id','f.method')
								  ->where('main_table.udropship_status = 1 AND b.address_type = "shipping" AND f.method ="cashondelivery" AND main_table.created_at >= DATE_SUB(NOW(),INTERVAL 60 DAY)');
//		echo $shipmentData->getSelect()->__toString(); exit; 
	$shipmentreport = $shipmentData->getData();
		foreach($shipmentreport as $_shipmentreport)
	    {
			sleep(10);
			echo 'Tracking Number: '.$_shipmentreport['number'];
			$vendoremail = $_shipmentreport['udropship_vendor'];
			$vendor = Mage::getModel('udropship/vendor')->load($vendoremail);
			$vendorname = $vendor['vendor_name'];
			$vendor_email = $vendor['email'];
			$custemail = $_shipmentreport['email'];
			$incrementid = $_shipmentreport['increment_id'];
			$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
			$track = 'http://track.delhivery.com/api/packages/xml/?token=063be5c01e3fa4d70bdd97fc4ad54495c478e7f8&waybill='.$_shipmentreport['number'];
			$doc = new DOMDocument();
			$doc->load($track);
			$docerror = $doc->documentElement;
			foreach($docerror->childNodes as $node){
				$error = $node->nodeValue;
			}	
			
		   if($error!='No such waybill or Order Id found')
			 {
				$delhiverytrack = $doc->getElementsByTagName("Status");
				foreach ($delhiverytrack as $_delhiverytrack)
				{
					$waybillstatus = $_delhiverytrack->getElementsByTagName("Status")->item(0)->nodeValue;			
					$sendEmailFlag = false;
					if($waybillstatus=='Delivered')
					{
					$sendEmailFlag = true;
					 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
							Mage::helper('udropship')->addShipmentComment($shipment,
									  ('Delivered to Customer by Logistic Service Provider'));
						$shipment->save();
					echo $subject = 'COD Shipment '.$incrementid.' has been successfully delivered to customer by our Logistic Service Provider!';
					$body = 'Dear Seller,<br><br> Your COD shipment '.$incrementid.' has been successfully delivered to customer by our Logistic Service Provider!';
											
					}
					elseif($waybillstatus=='Returned')
					{
					$sendEmailFlag = true;
					 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED_BY_CUSTOMER);
							Mage::helper('udropship')->addShipmentComment($shipment,
									  ('Returned by Customer to Logistic Service Provider'));
						$shipment->save();
						$body = 'Dear Seller,<br><br>Your COD Shipment '.$incrementid.' has been returned by customer to our Logistic Service Provider. Please contact the logistics service provider within 72 hrs, if you do not receive your shipment back.';
					echo 	$subject = 'COD Shipment '.$incrementid.' has been returned by customer to our Logistic Service Provider!';
										
					}
					elseif($waybillstatus=='RTO')
					{
					$sendEmailFlag = true;
					 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED_TO_SELLER);
							Mage::helper('udropship')->addShipmentComment($shipment,
									 ('Returned to Seller by Logistic Service Provider'));
						$shipment->save();
						$body = 'Dear Seller,<br><br> Your COD shipment '.$incrementid.' is being returned to you by our Logistic Service Provider. Please contact the logistics service provider within 72 hrs, if you do not receive your shipment back.';
					echo $subject = 'COD Shipment '.$incrementid.' is being returned to you by our Logistic Service Provider!';
					
					}

					if($sendEmailFlag)
					{
			     	$mail = Mage::getModel('core/email');
					$mail->setToName($vendorname);
					$mail->setToEmail($vendor_email);
					$mail->setBody($body);
					$mail->setSubject($subject);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Places");
					$mail->setType('html');
					
					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->send();
					}
					}
				}
			 }
		}


?>
