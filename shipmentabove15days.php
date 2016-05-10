<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$shipmentDetails = "SELECT * FROM `sales_flat_shipment`  WHERE `udropship_status` = '11' AND `created_at` < (DATE_SUB(NOW(),INTERVAL 30 DAY))";
$shipmentResult = $readcon->query($shipmentDetails)->fetchAll();
echo "Total Shipments Delayed".sizeof($shipmentResult); exit;
$templateId = 'cancel_email_to_customer_email_template';
$sender = Array('name'  => 'Craftsvilla',
		'email' => 'customercare@craftsvilla.com');
$emailCanceled = Mage::getModel('core/email_template');
$numcodcan = 0;
$numprepaidoos = 0;
$k =0;
foreach($shipmentResult as $_shipmentResult)
{
if($k < 2)
{
		$orderIDD = $_shipmentResult['order_id'];
		//echo $orderIDD1 = $_shipmentResult['entity_id'];exit;
		$shipment25102014 = Mage::getModel('sales/order_shipment')->load($_shipmentResult['entity_id']);
		$_shipmentId = $shipment25102014->getIncrementId();
		echo "Working on Shipment:".$_shipmentId;
		$getOrderData = Mage::getModel('sales/order')->load($orderIDD);
		$orderData = $getOrderData->getPayment()->getMethod();
		$shippingData = $getOrderData->getShippingAddress();
		$name = $shippingData->getFirstname().' '.$shippingData->getLastname();
		$_orderBillingEmail = $getOrderData->getBillingAddress()->getEmail();
		$dropship = Mage::getModel('udropship/vendor')->load($shipment25102014->getUdropshipVendor());
		$vendorId = $dropship->getVendorId();
		$vendorName = $dropship->getVendorName();
		$vendorEmail = $dropship->getEmail();
		$vendorTelephone = $dropship->getTelephone();
		$closingBalance = $dropship->getClosingBalance();
		$baseTotalValue = $shipment25102014->getBaseTotalValue();
		$penaltyAmount = ($baseTotalValue*0.02);
		$closingBalance = $closingBalance-$penaltyAmount;

			if($orderData == 'cashondelivery')
			{
				echo "Cancelling the COD Order";
				$numcodcan++;
				$updateshipmentstatus= "update `sales_flat_shipment` set `udropship_status`='6'  WHERE `entity_id`= '".$_shipmentResult['entity_id']."'";
				$write->query($updateshipmentstatus);

				$comment25102014 = Mage::getModel('sales/order_shipment_comment')
					->setParentId($_shipmentResult['entity_id'])
					->setComment('Shipment cancelled by system because of delay in processing')
					->setCreatedAt(NOW())
					->save();
				$shipment25102014->addComment($comment25102014);
				$currencysym = Mage::app()->getLocale()->currency($getOrderData->getBaseCurrencyCode())->getSymbol();
				$_items = $shipment25102014->getAllItems();
				$customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				//echo $_item['product_id'];exit;
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
				try{
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				}catch(Exception $e){}
				$customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_shipmentId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
				$customerShipmentItemHtml .= "</table>";
				$vars = array('shipmentid'=>$_shipmentId,
							'vendorShopName'=>$vendorName,
							'selleremail' => $vendorEmail,
							'sellerTelephone' => $vendorTelephone,
							'customershipmentitemdetail' =>	$customerShipmentItemHtml,
							'custfirstname' => $name
							);
				//print_r($vars);exit;
				$emailCanceled->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							  ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
				$message = "Craftsvilla.com : Your Delayed Shipment ".$_shipmentId." Has Been Canceled Automatically By System !";
				$body = "Dear ".$vendorName." <br/>Your shipment ".$_shipmentId." has been canceled  because of delay in shipping the product. You will be charged a penalty if there are excess cancellation of your shipments!";

				$mail = Mage::getModel('core/email');
				$mail->setToName($vendorName);
				$mail->setToEmail($vendorEmail);
				$mail->setBody($body);
				$mail->setSubject($message);
				$mail->setFromEmail('customercare@craftsvilla.com');
				$mail->setFromName("CustomerCare");
				$mail->setType('html');
				$mail->send();
				echo 'Email sent Successfully-shipmentId is: '.$_shipmentId;
			}
			else
			{
			$numprepaidoos++;
				$updateshipmentstatus= "update `sales_flat_shipment` set `udropship_status`='18'  WHERE `entity_id`= '".$_shipmentResult['entity_id']."'";
				$write->query($updateshipmentstatus);
				$comment25102014 = Mage::getModel('sales/order_shipment_comment')
								->setParentId($_shipmentResult['entity_id'])
				    			->setComment('Shipment changed to out of stock by system beacause of delay in processing and seller is charged penalty of Rs.'.$penaltyAmount)
								->setCreatedAt(NOW())
								->save();
				$shipment25102014->addComment($comment25102014);

				$queryUpdateForClosingbalance = "update `udropship_vendor` set `closing_balance`='".$closingBalance."'  WHERE `vendor_id`= '".$vendorId."'";
				$write->query($queryUpdateForClosingbalance);

				//Added by Ankit for Panalty Invoice Implementation
				$today = date("Y-m-d H:i:s");
				$queryUpdatePenalty = "INSERT INTO `udropship_vendor_penalty_cv`(`penalty_id`, `increment_id`, `penalty_amount`, `penalty_waiveoff`, `created_at`, `updated_at`) VALUES ('DEFAULT','".$_shipmentId."','".$penaltyAmount."','0','".$today."','".$today."' )";
				$write->query($queryUpdatePenalty);
				$write->closeConnection();
				//End Ankit Addtion

			$message = "Craftsvilla.com : Your Shipment ".$_shipmentId." Status Has Been Changed To Out Of Stock Automatically By System !";
			$body = "Dear ".$vendorName." <br/>Your shipment ".$_shipmentId." has been changed to out of stock  because of delay in shipping the product. You have been charged penalty of Rs.".$penaltyAmount." based on your shipment value . If you have any question please email to us at places@craftsvilla.com !";

			$mail = Mage::getModel('core/email');
			$mail->setToName($vendorName);
			$mail->setToEmail($vendorEmail);
			$mail->setBody($body);
			$mail->setSubject($message);
			$mail->setFromEmail('customercare@craftsvilla.com');
			$mail->setFromName("CustomerCare");
			$mail->setType('html');
			$mail->send();
			echo 'Email sent Successfully-shipmentId is: '.$_shipmentId;
			}
}
$k++;
}
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Total Delayed Orders:".$k.", COD Cancelled: ".$numcodcan.", Prepaid Out Of Stock:".$numprepaidoos." At Time ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

