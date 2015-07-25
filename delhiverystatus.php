<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection()
									->addAttributeToFilter('udropship_status', '24');
									//echo '<pre>';print_r($shipmentCollection->getData());exit;

foreach($shipmentCollection as $_shipmentCollection)

{
	$incrementid = $_shipmentCollection->getIncrementId();
	$_shipmentCollection->getUdropshipStatus();
	$_shipmentCollection->getUdropshipVendor();
	$orderid = $_shipmentCollection->getOrderId();
	$track = "select `number` from `sales_flat_shipment_track` where `order_id` = '".$orderid."'";
	$tracknumber = $db->query($track)->fetchAll();
	//while($row = mysql_fetch_array($tracknumber))
	foreach($tracknumber as $row)
	{
		$trackingnumber = $row['number'];
	}
	$method = "select `method` from `sales_flat_order_payment` where `parent_id` = '".$orderid."'";
	$result = $db->query($method)->fetchAll();
	//while($row1 = mysql_fetch_array($result))
	foreach($result as $row1)
	{
		$row1['method'];
		if($row1['method']=='cashondelivery')
	{
		
		$shipment= Mage::getModel('sales/order_shipment')->load($_shipmentCollection->getId());
		if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
			
			$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
				
		}
		Mage::helper('udropship')->addShipmentComment($shipment,
		  ('Shipment has been complete by System')
							  );
		$shipment->save();
		$shipment->sendEmail(true, '')
            ->setEmailSent(true)
            ->save(); 
		/*$storeId = Mage::app()->getStore()->getId();
		$templateId = 'sales_email_shipment_template';
		$sender = Array('name'  => 'Craftsvilla',
		'email' => 'places@craftsvilla.com');
		$translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$_email = Mage::getModel('core/email_template');
		$mailSubject = 'Craftsvilla.com: Your Shipment # '.$incrementid.' has been Shipped';
		$shipmentHtml = '';
					
		$vars = Array('shipmentHtml' =>$updateHtml, 'shipmentid' => $incrementid);		
					
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				->setTemplateSubject($mailSubject)
				->sendTransactional($templateId, $sender, $emailAddress, $recname, $vars, $storeId);
			$translate->setTranslateInline(true);*/
		echo "Email has been sent successfully";
							 
	
		
							    
		 
	}
	
	}
	
	
}
?>
