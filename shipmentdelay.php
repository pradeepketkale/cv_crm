<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$vendorname = $vendor['vendor_name'].'<br>'; 
$vendortelephone = $vendor['telephone'].'<br>';
$vendoremail = $vendor['email'].'<br>';
$_todayDate = date('jS F Y');
$timestamptoday = Mage::getModel('core/date')->timestamp(time())-(24*3600*6);
$timestamptoday1 = Mage::getModel('core/date')->timestamp(time())-(24*3600*5);
$newdate = date('Y-m-d h:m:s',$timestamptoday);
$newdate1 = date('Y-m-d h:m:s',$timestamptoday1);

			$shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
									->addAttributeToFilter('udropship_status', '11')
									->addAttributeToFilter('created_at', array('from' => $newdate, 'to' => $newdate1));
				//echo $shipmentCollection->getSelect();exit;
			//	echo $shipmentCollection->count();exit;
			//echo '<pre>';print_r($shipmentCollection->getData());exit;
			
		foreach($shipmentCollection as $_shipmentCollection)
			{
				$_shippmentOrder = $_shipmentCollection->getOrder();
				$_items = $_shippmentOrder->getAllVisibleItemsByVendor($_shipmentCollection->getUdropshipVendor());
				$_orderCustFirstName = $_shippmentOrder->getCustomerFirstname();
				$_orderCustLastName = $_shippmentOrder->getCustomerLastname();
				$_shipmentIncrementId = $_shipmentCollection->getIncrementId();
				$vendor = $_shipmentCollection->getUdropshipVendor();
				$_shippmentCreateDate = $_shipmentCollection->getCreatedAt();
				$orderid = $_shipmentCollection->getOrderId();
				$order = Mage::getModel('sales/order')->load($orderid);
				$shipping_address = $order->getShippingAddress();
				$countryId = $shipping_address->getCountryId();
				$incmntId = $order->getIncrementId();
				$customerEmail = $order->getCustomerEmail();
				$vendorid = Mage::getModel('udropship/vendor')->load($vendor);
				$vendoremail = $vendorid->getEmail();
				$vendorname = $vendorid->getVendorName();
				$vendorphone = $vendorid->getTelephone();
				$storeId = Mage::app()->getStore()->getId();	  
						$templateId = 'shipmentdelay_email_template';
						$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
						//$translate  = Mage::getSingleton('core/translate');
						//$translate->setTranslateInline(false);
						$_email = Mage::getModel('core/email_template');
						$mailSubject = 'Your Shipment From Seller '.$vendorname.' Is Being Worked Upon By Us At Top Priority!';
						$shipmentHtml = '';
       
				 $shipmentHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendorname."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendoremail."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$vendorphone."</td></tr></table>";
				$vendorShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
							
				$_items1 = Mage::getModel('sales/order_shipment')->loadByIncrementId($_shipmentIncrementId);
				$all = $_items1->getAllItems();
				foreach ($all as $_item)
				{
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";		
		$vars = Array('shipmentId' => $_shipmentIncrementId,
					'shipmentDate' => date('jS F Y',strtotime($_shippmentCreateDate)),
					'customerName' =>$_orderCustFirstName." ".$_orderCustLastName,
					'vendorName' =>$vendorname,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
					'shipmentHtml' =>$shipmentHtml, 'shipmentno' => $_shipmentIncrementId, 'selleremail' => $vendoremail, 'sellername' => $vendorname, 'sellertelephone' => $vendorphone, 'customername' => $_orderCustFirstName
				);	
				if($countryId == 'IN')
				{
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, $customerEmail, $recname, $vars, $storeId);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId, $sender, 'manoj@craftsvilla.com', $recname, $vars, $storeId);
					//print_r($_email);exit;
					//$translate->setTranslateInline(true);
					echo "Email has been sent successfully";
				}
				
			}
			


?>
