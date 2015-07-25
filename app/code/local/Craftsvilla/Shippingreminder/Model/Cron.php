<?php
class Craftsvilla_Shippingreminder_Model_Cron
{
	public function sendreminder()
	{
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'shippingreminder_email_template';
		$mailSubject = 'Action Required – Enter Shipping Details of Your Item(s) Sold on Craftsvilla';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
		$vendors = Mage::getModel('udropship/vendor')->getCollection()
					->addFilter('status', array('eq' => 'A'));
					//->addFilter('vendor_id', array('eq' => '1'));
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
		foreach($vendors as $vendor){
			$vendorShippHandling = array();
			$vendorShipmentItemHtml = '';
			$vendorInfo = $vendor->load($vendor->getVendorId());
			$vendorShippHandling = explode(" ",$vendorInfo->getShipHandlingTime());
			if($vendorShippHandling[1] == 'hours')
					$vendorShippHandling[0] = $vendorShippHandling[0]/24;
			$shippmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
									->addAttributeToFilter('udropship_vendor', $vendor->getVendorId())
									->addAttributeToFilter('udropship_status', '11');
			foreach($shippmentCollection as $_shipment){
				$_shippmentCreateDate = '';
				$_datediff = '';
				//$_previousItemSku = '';

				$newdate = '';
				$delta = '';
				$newdateTime = '';
				$_shippmentCreateDate = $_shipment->getCreatedAt();
				$_datediff = "+".$vendorShippHandling[0]." day";
				$newdate = strtotime($_shippmentCreateDate);
				$newdate = strtotime($_datediff, $newdate);
				$newdate =  date('jS F Y', $newdate);
				
				$_todayDate = date('jS F Y');
				$_todayTime = strtotime($_todayDate);
				$newdateTime = strtotime($newdate);
				$delta = $newdateTime - $_todayTime;
				//$_items = $_shipment->getAllItems();
				$_shippmentOrder = $_shipment->getOrder();
				$_items = $_shippmentOrder->getAllVisibleItemsByVendor($_shipment->getUdropshipVendor());
				$_orderCustFirstName = $_shippmentOrder->getCustomerFirstname();
				$_orderCustLastName = $_shippmentOrder->getCustomerLastname();
				$_shipmentIncrementId = $_shipment->getIncrementId();
				//$_orderIncrementId = $_shippmentOrder->getIncrementId();

				foreach ($_items as $_item){
					//if($_previousItemSku == $_item->getSku() and $_previousItemSku!='') continue;
					if ($delta <= 60 * 60 *24)
						$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$newdate."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_orderCustFirstName." ".$_orderCustLastName."</td></tr>";
					else
						$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;'><a href='www.craftsvilla.com/marketplace'>".$_shipmentIncrementId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;'>".$newdate."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;'>".$_orderCustFirstName." ".$_orderCustLastName."</td></tr>";

					//$_previousItemSku = $_item->getSku();
				}
				
			}
			if($vendorShipmentItemHtml!=''){
				$vars = Array('vendorItemHTML' =>$vendorShipmentItemHtml,
					'vendorName' =>$vendorInfo->getVendorName(),
				);
				
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
						->sendTransactional($templateId, $sender, $vendorInfo->getEmail(), $vendorInfo->getVendorName(), $vars, $storeId);
				$translate->setTranslateInline(true);
				$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
				$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
				$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
				$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to Vendor
				$customerMessage = 'You have shipments pending to be shipped. Please ship immediately or they will refunded.';
				$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$vendorInfo->getTelephone()."&source=".$_smsSource."&message=".urlencode($customerMessage);
				$parse_url = file($_customerSmsUrl);	
			}
		}
	}

	public function sendcouponsreminder()
	{
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'signupvoucher_reminder_email_template';
		$mailSubject = 'Action Required – Enter Shipping Details of Your Item(s) Sold on Craftsvilla';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'customercare@craftsvilla.com');
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');

		$now = Mage::getModel('core/date')->timestamp(time());
		$now = strtotime("-5 day", $now);
		$dateStart = date('Y-m-d' . ' 00:00:00', $now);
		$dateEnd = date('Y-m-d' . ' 23:59:00', $now);
		$customers = Mage::getModel('customer/customer')->getCollection()
						->addNameToSelect()
						->addFieldToFilter('created_at', array('from' => $dateStart, 'to' => $dateEnd))
						->addAttributeToFilter('website_id', '1');

		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		foreach($customers as $customer){
			$query = "SELECT * FROM salesrule_coupon as sc LEFT JOIN salesrule_coupon_usage as scu ON sc.coupon_id = scu.coupon_id WHERE sc.code = 'MUSTARD' AND scu.customer_id= '".$customer->getId()."' AND scu.times_used='1'";
			$data = $read->fetchAll($query);
			if(empty($data)){
				$vars = array('customername' => $customer->getData('name'));
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->setTemplateSubject($mailSubject)
						->sendTransactional($templateId, $sender, $customer->getEmail(), $customer->getData('name'), $vars, $storeId);
				$translate->setTranslateInline(true);
			}
		}
	}
}
?>
