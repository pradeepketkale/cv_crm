<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Seller Shipping Reminder Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

//$sendEmailFlag = false;
//$sendSmsFlag = false;
$sendEmailFlag = true;
$sendSmsFlag = true;


		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'shippingreminder_email_template';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
		//$vendors = Mage::getModel('udropship/vendor')->getCollection()
			//		->addFilter('status', array('eq' => 'A'));
		//$translate  = Mage::getSingleton('core/translate');
		//$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
                $summaryShipmentItemHtml = '';
                $shipmentNumDelayed = 0;

		$timestamptoday = Mage::getModel('core/date')->timestamp(time());
		$today = date('Y-m-d h:m:s',$timestamptoday);
		/*$timestampfrom = Mage::getModel('core/date')->timestamp(time())-(24*3600*5);
		$from_date = date('Y-m-d h:m:s',$timestampfrom);*/
		$i = 0;
			//$vendorShipmentItemHtml = '';
			//$vendorShipmentItemHtmlSum = '';
			//$vendorInfo = Mage::getModel('udropship/vendor')->load($vendor->getVendorId());
			$shippmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
								//->addAttributeToFilter('udropship_vendor', 458)	
									->addAttributeToFilter('udropship_status', '11');
									//->addAttributeToFilter('created_at', array( 'from' >= $from_date,'to' => $today, 'date' => true));
			 $shippmentCollection->getSelect()->order('main_table.created_at ASC');
			echo 'Total Processing Shipments';
			echo $shippmentCollection->count();
			//echo $shippmentCollection->getSelect()->__toString();exit;
		
		foreach($shippmentCollection as $_shipment){
			$vendorShipmentItemHtml = '';
			$vendorShipmentItemHtmlSum = '';
			$_shipmentIncrementId = $_shipment->getIncrementId();
			$shippmentExpCollection = Mage::getModel('activeshipment/activeshipment')->getCollection()
									->addFieldToFilter('shipment_id', $_shipmentIncrementId)
									->addFieldToFilter('expected_shipingdate',array('gteq'=>$today));
											//echo $shippmentExpCollection->getSelect()->__toString();exit;	
				$shipvendorId = $_shipment->getUdropshipVendor();
				$shippmentCollectionNew = Mage::getModel('sales/order_shipment')->getCollection()
                                                                ->addAttributeToFilter('udropship_vendor', $shipvendorId)
								->addAttributeToFilter('udropship_status', array('IN' => array(1,7,15)));
				 $shippedLabel = "First Shipment";
				if($shippmentCollectionNew->count() > 0) $shippedLabel = "Has Shipped Once";

				$shippmentCollectionRefund = Mage::getModel('sales/order_shipment')->getCollection()
                                                                ->addAttributeToFilter('udropship_vendor', $shipvendorId)
                                                                ->addAttributeToFilter('udropship_status', array('IN' => array(12,18,19,23)));
				$shippmentCollectionTotal = Mage::getModel('sales/order_shipment')->getCollection()
                                                                ->addAttributeToFilter('udropship_vendor', $shipvendorId);

				$refundRatio = ($shippmentCollectionRefund->count()/$shippmentCollectionTotal->count())*100;
				$totalShipmentVendor = $shippmentCollectionTotal->count();
				$refundRatio = round($refundRatio);
				unset($shippmentCollectionNew);
				unset($shippmentCollectionRefund);
				unset($shippmentCollectionTotal);
				if($shippmentExpCollection->count() == 0)
				{
				$newdate = '';
				$delta = '';
				$newdateTime = '';
				$_shippmentCreateDate = $_shipment->getCreatedAt();
				$newdate = strtotime($_shippmentCreateDate)+5*24*60*60;
			    	$newdate1 =  date('jS F Y', $newdate);
				
				$_todayDate = date('jS F Y');
				$_todayTime = strtotime($_todayDate);
				$expecteddate1 = date('dmy', $_todayTime); 
				$expecteddate1dis = date('jS F ', $_todayTime); 
				
				$_todayTime1 = strtotime($_todayDate)+1*24*60*60;
				$expecteddate2 = date('dmy', $_todayTime1); 
				$expecteddate2dis = date('jS F', $_todayTime1);
				
				$_todayTime2 = strtotime($_todayDate)+2*24*60*60;
				$expecteddate3 = date('dmy', $_todayTime2); 
				$expecteddate3dis = date('jS F', $_todayTime2);
				
				$_todayTime3 = strtotime($_todayDate)+3*24*60*60;
				$expecteddate4 = date('dmy', $_todayTime3); 
				$expecteddate4dis = date('jS F', $_todayTime3);
				
				$_todayTime4 = strtotime($_todayDate)+4*24*60*60;
				$expecteddate5 = date('dmy', $_todayTime4); 
				$expecteddate5dis = date('jS F', $_todayTime4);
				
				$delta = $_todayTime - $newdate;
				if ($delta >= 0)
				{
				$shipmentNumDelayed++;
                                echo 'Shipments Delayed: '.$shipmentNumDelayed;
				$delay = (ceil($delta/(60*60*24))+5);
				$delay1 = (ceil($delta/(60*60*24))+5)." Days";
				if ($delay > 10)
				{	
					$mailSubject = 'Final Warning!!! – Enter Shipping Details of Your Item(s) Sold on Craftsvilla Immediately or They Will Be Refunded';
				}
				else if ($delay > 7)
				{
					 $mailSubject = 'Extreme Delay!! – Enter Shipping Details of Your Item(s) Sold on Craftsvilla Before They Get Refunded';
				}
				else
				{
					$mailSubject = 'Shipments Delayed! – Enter Shipping Details of Your Item(s) Sold on Craftsvilla';
				}
				
				$_shippmentOrder = $_shipment->getOrder();
				$_items = $_shippmentOrder->getAllVisibleItemsByVendor($_shipment->getUdropshipVendor());
				$_orderCustFirstName = $_shippmentOrder->getCustomerFirstname();
				$_orderCustLastName = $_shippmentOrder->getCustomerLastname();
				$shiporderId = $_shipment->getOrderId();
				//$shipvendorId = $_shipment->getUdropshipVendor();
				$vendorInfo = Mage::getModel('udropship/vendor')->load($shipvendorId);
				 $vendorName = $vendorInfo->getVendorName();
				$urlactionday1 = Mage::getBaseUrl().'umicrosite/vendor/replyshipmentdate?q='.$shiporderId.'&date='.$expecteddate1.'&vendorid='.$shipvendorId.'&shipmentid='.$_shipmentIncrementId;
				$urlactionday2 = Mage::getBaseUrl().'umicrosite/vendor/replyshipmentdate?q='.$shiporderId.'&date='.$expecteddate2.'&vendorid='.$shipvendorId.'&shipmentid='.$_shipmentIncrementId;
				$urlactionday3 = Mage::getBaseUrl().'umicrosite/vendor/replyshipmentdate?q='.$shiporderId.'&date='.$expecteddate3.'&vendorid='.$shipvendorId.'&shipmentid='.$_shipmentIncrementId;
				$urlactionday4 = Mage::getBaseUrl().'umicrosite/vendor/replyshipmentdate?q='.$shiporderId.'&date='.$expecteddate4.'&vendorid='.$shipvendorId.'&shipmentid='.$_shipmentIncrementId;
				$urlactionday5 = Mage::getBaseUrl().'umicrosite/vendor/replyshipmentdate?q='.$shiporderId.'&date='.$expecteddate5.'&vendorid='.$shipvendorId.'&shipmentid='.$_shipmentIncrementId;
				
				foreach ($_items as $_item){
						if ($refundRatio > 25) $refundText = "<td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#FF0000;color:#CE3D49;'>".$refundRatio."%</td>";
						else
					$refundText = "<td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$refundRatio."%</td>";

					if(time() > strtotime('+3 month', strtotime($_item->getUpdatedAt())))
						echo $updatedAtText = "<td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#FF0000;color:#CE3D49;'>".$_item->getUpdatedAt()."</td>";
					else
						$updatedAtText = "<td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getUpdatedAt()."</td>";
				
						$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$delay1."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_orderCustFirstName." ".$_orderCustLastName."</td></tr>";
						$vendorShipmentItemHtmlSum .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()." (".$_item->getPrice().")</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$delay1."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendorName."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendorInfo->getTelephone()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendorInfo->getEmail()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shippedLabel."</td>".$refundText."<td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$totalShipmentVendor."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_shipment->getBaseTotalValue()."</td>".$updatedAtText."</tr>";
					}
			//echo $vendorShipmentItemHtml;exit; 	
				
$checkbox = '<a href ="'.$urlactionday1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="sndship"  > '.$expecteddate1dis.'</button></a>';
$checkbox .= '<a href ="'.$urlactionday2.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;" type="button" name="sndship" > '.$expecteddate2dis.'</button></a>';
$checkbox .= '<a href ="'.$urlactionday3.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;" type="button" name="sndship" > '.$expecteddate3dis.'</button></a>';
$checkbox .= '<a href ="'.$urlactionday4.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;" type="button" name="sndship" > '.$expecteddate4dis.'</button></a>';
$checkbox .= '<a href ="'.$urlactionday5.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;" type="button" name="sndship" > '.$expecteddate5dis.'</button></a>';
				if($vendorShipmentItemHtml!=''){
				$vars = Array('vendorItemHTML' =>$vendorShipmentItemHtml,
							  'vendorName' =>$vendorInfo->getVendorName(),
							  'chkbox' => $checkbox,
							  );
				//echo '<pre>';print_r($vars);
				if($sendEmailFlag)
				{
				try {
                                echo "Sending Email To Vendor";
				echo $vendorInfo->getEmail();
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, $vendorInfo->getEmail(), $vendorInfo->getVendorName(), $vars, $storeId);
				 }
                		catch (Exception $e) {
					echo "Unable to Send Email To Vendor";
					echo $e->getMessage();
				}

				 //sleep(2);

// CC to manoj@craftsvilla.com
				 try {
                                echo "Sending Email To Manoj";
                                $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                ->setTemplateSubject($mailSubject)
                                                ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $vendorName, $vars, $storeId);
				 }
                                catch (Exception $e) {
                                        echo "Unable to Send Email To Manoj";
                                        echo $e->getMessage();
                                }
				//$translate->setTranslateInline(true);
				}

				if($sendSmsFlag)
				{
					$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$vendorInfo->getTelephone()."&source=".$_smsSource."&message=".urlencode($customerMessage);
					$parse_url = file($_customerSmsUrl);								
				}
				}
				else {echo 'Not Sending Email As No Items \n';}
				}
			}
 		//	unset($shippmentCollection);
                	$summaryShipmentItemHtml .= $vendorShipmentItemHtmlSum;
		}
	
  $vars = Array('vendorItemHTML' =>$summaryShipmentItemHtml,
                                                          'vendorName' => "Manoj Gupta"
                                                        );
// CC to manoj@craftsvilla.com
                echo $mailSubjectSummary = 'Note: Total Shipments Delayed Over 5 Days: '.$shipmentNumDelayed;
                $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                ->setTemplateSubject($mailSubjectSummary)
                                                ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', 'Manoj', $vars, $storeId);
                $_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                                                ->setTemplateSubject($mailSubjectSummary)
                                                ->sendTransactional($templateId, $sender, 'monica@craftsvilla.com', 'Monica', $vars, $storeId);

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Seller Shipping Reminder Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
		
