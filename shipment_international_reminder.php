<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Seller Reminder Script For QC Rejected and Shipped to Craftsvilla Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();


		$shippmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
								->addAttributeToFilter('udropship_status', array('in' => array(15,16)));
								
//			echo $shippmentCollection->count();exit;
		//	echo $shippmentCollection->getSelect()->__toString();exit;
		
		foreach($shippmentCollection as $_shipment){
			$vendorShipmentItemHtml = '';
			$vendorShipmentItemHtmlSum = '';
			$_shipmentIncrementId = $_shipment->getIncrementId();
			$newdate = '';
			$delta = '';
			$newdateTime = '';
			$_shippmentUpdatedDate = $_shipment->getUpdatedAt();
			
			$shipstatus = $_shipment->getUdropshipStatus();
			
			
			
			$newdate = strtotime($_shippmentUpdatedDate)+5*24*60*60;
			
			$shipmentdelay = date('dmy', $newdate); 
		    $shipmentdelay1 = date('jS F', $newdate);
			$newdate1 =  date('jS F Y', $newdate);
			$_todayDate = date('jS F Y');
			$_todayTime = strtotime($_todayDate);
			
			$delta = $_todayTime - $newdate;
			
			
			if ($delta >= 0)
				{
				
				if (($shipstatus == 16))
				{	
				    $templateId = 'qcrejected_email_template1';
					$mailSubject = 'Action Required: QC Rejected Shipment '.$_shipmentIncrementId.' Status Not Yet Updated To Us!';
				}
				else if (($shipstatus == 15))
				{
					$templateId = 'shippedtocraftsvilla_email_template1';
					$mailSubject = 'Action Required: Shipped To Craftsvilla Shipment '.$_shipmentIncrementId.' Not Yet Received By Craftsvilla!';
				}
				
				
				$_shippmentOrder = $_shipment['order_id'];
				$order = Mage::getModel('sales/order')->load($_shippmentOrder);
				
				$_orderCustFirstName = $order->getCustomerFirstname();
				$_orderCustLastName = $order->getCustomerLastname();
				$shiporderId = $_shipment->getOrderId();
				$shipvendorId = $_shipment->getUdropshipVendor();
				$vendorInfo = Mage::getModel('udropship/vendor')->load($shipvendorId);
				$vendorName = $vendorInfo->getVendorName();
				$vendoremail = $vendorInfo->getEmail();	
				$ShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				$_items1 = Mage::getModel('sales/order_shipment')->loadByIncrementId($_shipmentIncrementId);
				$all = $_items1->getAllItems();
				foreach ($all as $_item)
				{
					
				$product = Mage::getModel('catalog/product')->load($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
				 $ShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".floor($_item->getPrice())."</td></tr>";
				}
		$ShipmentItemHtml .= "</table>";	
		
		$storeId = Mage::app()->getStore()->getId();
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
	   $translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
	$vars = Array('vendorItemHTML' =>$ShipmentItemHtml,
							  'vendorName' =>$vendorInfo->getVendorName(),
							  'shipmentid' => $_shipmentIncrementId,
							  
							  );
	$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, $vendoremail, $vendorName, $vars, $storeId);

	//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
       //                                         ->setTemplateSubject($mailSubject)
       //                                 ->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $vendorName, $vars, $storeId);
                  $translate->setTranslateInline(true);
   
         echo 'Email has been send successfully';
         
		}
				
	}
	
                   
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Seller Shipping Reminder Script Ended at Time:: ".$currentTime;

		
