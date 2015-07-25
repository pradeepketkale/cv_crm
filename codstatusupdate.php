<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

		$shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
		$shipmentData->getSelect()->join(array('d'=>'sales_flat_shipment_track'), 'd.parent_id=main_table.entity_id','d.number')
								  ->where('main_table.udropship_status IN (24,27,28)');
 //echo $shipmentData->getSelect()->__toString(); 
	$shipmentreport = $shipmentData->getData(); 
	echo 'Total Shipments to check'.$shipmentData->count();
//	echo Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED; exit;
		foreach($shipmentreport as $_shipmentreport)
	    {
			sleep(10);
			$vendoremail = $_shipmentreport['udropship_vendor'];
			$vendor = Mage::getModel('udropship/vendor')->load($vendoremail);
			$vendorname = $vendor['vendor_name'];
			$vendor_email = $vendor['email'];
			$custemail = $_shipmentreport['email'];
			echo $incrementid = $_shipmentreport['increment_id'];
			echo 'Tracking Number: ';
  			echo $awbNumberDel = trim($_shipmentreport['number']);
			$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($incrementid);
			$shipmentStatus = $shipment->getUdropshipStatus();
			$track = 'http://track.delhivery.com/api/packages/xml/?token=063be5c01e3fa4d70bdd97fc4ad54495c478e7f8&waybill='.$awbNumberDel;
			$doc = new DOMDocument();
			$doc->load($track);
			$docerror = $doc->documentElement;
			foreach($docerror->childNodes as $node){
				$error = $node->nodeValue;
			}	
			//echo $error;
			
		   if($error!='No such waybill or Order Id found')
			 {
				echo "Found No Error";
				 $delhiverytrack = $doc->getElementsByTagName("Status");
				 //$instructionNode = $doc->getElementsByTagName("Status");
				foreach ($delhiverytrack as $_delhiverytrack)
				{
					echo $waybillstatus = $_delhiverytrack->getElementsByTagName("Status")->item(0)->nodeValue;			
					echo $instructions = $_delhiverytrack->getElementsByTagName("Instructions")->item(0)->nodeValue; 		
					$sendEmailFlag = false;
					if($waybillstatus=='In Transit' || $waybillstatus=='Dispatched' || $waybillstatus=='Pending')
					{
						if($waybillstatus=='In Transit')
						{
							if($instructions =='Shipment received')
							{
								if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP)
								{
								$sendEmailFlag = true;
                                                        	$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP);
                                                        	Mage::helper('udropship')->addShipmentComment($shipment,
                                                                         ('Shipment Picked Up by Logistic Service Provider'));
                                                		$shipment->save();

                                                		$subject = 'COD Shipment '.$incrementid.' Has Been Picked From You By Our Logistic Service Provider!';
                                        			$body = 'Dear Seller,<br><br> This is to inform you that according to our records your COD shipment '.$incrementid.' has been collected from you by our Logistic Service Provider!';
								}

							}
							else
							{
								if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_MANIFEST_SHARED)
								{
								$sendEmailFlag = true;
		                                        	$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_MANIFEST_SHARED);
                                                        	Mage::helper('udropship')->addShipmentComment($shipment,
                                                                         ('Manifest Shared With Our Logistic Service Provider'));
                                                		$shipment->save();
					 			$subject = 'Details of your COD Shipment '.$incrementid.' has been shared by us to our Logistic Service Provider!';
								$body = 'Dear Seller,<br><br> This is to inform you that we have shared the details of your COD shipment '.$incrementid.' with our Logistics Service Provider. Please keep COD package ready for pickup and paste the packing label at the front of the package. The AWB number and tracking bar code are there on the right top corner of the packing label. The packing label is also called Manifest by the courier partners. You can also request pickup from the vendor panel. ';
								}
							}
						}
						else if($waybillstatus=='Pending')
						{
							if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP)
							{
							$sendEmailFlag = true;
		                                        $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_COD_SHIPMENT_PICKED_UP);
                                                        Mage::helper('udropship')->addShipmentComment($shipment,
                                                                         ('Shipment Picked Up by Logistic Service Provider'));
                                                	$shipment->save();

							$subject = 'COD Shipment '.$incrementid.' Has Been Picked From You By Our Logistic Service Provider!';
                                        		$body = 'Dear Seller,<br><br> This is to inform you that according to our records your COD shipment '.$incrementid.' has been collected from you by our Logistic Service Provider!';
							}
						}
						else
						{
							if($shipmentStatus != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED)
							{
							$subject = 'COD Shipment '.$incrementid.' Is On Its Way To Customer By Our Logistic Service Provider!';
                                        		$body = 'Dear Seller,<br><br> This is to inform you that according to our records your COD shipment '.$incrementid.' is being attempted for delivery to customer by our Logistic Service Provider!';
							$sendEmailFlag = true;
                                          		$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
                                                        Mage::helper('udropship')->addShipmentComment($shipment,
                                                                         ('Shipped to Customer by Logistic Service Provider'));
                                                	$shipment->save();
                                                	$shipment->sendEmail(true, '')
                                                                ->setEmailSent(true)
                                                                ->save();
							}
						}
						
					}
					elseif($waybillstatus=='Delivered')
					{
					$sendEmailFlag = true;
					 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
							Mage::helper('udropship')->addShipmentComment($shipment,
									  ('Delivered to Customer by Logistic Service Provider'));
						$shipment->save();
					$subject = 'COD Shipment '.$incrementid.' has been successfully delivered to customer by our Logistic Service Provider!';
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
						$subject = 'COD Shipment '.$incrementid.' has been returned by customer to our Logistic Service Provider!';
										
					}
					elseif($waybillstatus=='RTO')
					{
					$sendEmailFlag = true;
					 $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED_TO_SELLER);
							Mage::helper('udropship')->addShipmentComment($shipment,
									 ('Returned to Seller by Logistic Service Provider'));
						$shipment->save();
						$body = 'Dear Seller,<br><br> Your COD shipment '.$incrementid.' is being returned to you by our Logistic Service Provider. Please contact the logistics service provider within 72 hrs, if you do not receive your shipment back.';
					$subject = 'COD Shipment '.$incrementid.' is being returned to you by our Logistic Service Provider!';
					
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
