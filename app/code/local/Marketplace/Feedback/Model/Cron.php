<?php
class Marketplace_Feedback_Model_Cron
{
	public function sendfollowreminder()
	{
		$read = Mage::getSingleton('core/resource')->getConnection('feedback_read');
		$sql = "SELECT shipment_id FROM feedback_reminder WHERE follow_reminder_date = '2012-06-19' AND status = '0' AND  NOT EXISTS (
	SELECT shipment_id 
	FROM feedback_vendor_shipping
	WHERE feedback_vendor_shipping.shipment_id = feedback_reminder.shipment_id AND feedback_type = 1
)";
		$feedbackshipmentData = $read->fetchAll($sql);
		if($feedbackshipmentData){
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'feedback_reminder_email_template';
			$sender = Array('name'  => 'Craftsvilla',
						'email' => 'messages@craftsvilla.com');
			$_email = Mage::getModel('core/email_template');
			$shipment = Mage::getModel('sales/order_shipment');
			$customer = Mage::getModel('customer/customer');
			$write = Mage::getSingleton('core/resource')->getConnection('feedback_write');
			foreach($feedbackshipmentData as $feedbackshipment){
				$shipmentData = '';
				$customerData = '';
				$shipmentData = $shipment->load($feedbackshipment['shipment_id']);
				$customerData = $customer->load($shipmentData->getCustomerId());
				$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
							'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getFirstname()." ".$customerData->getLastname(),
				);
				$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->sendTransactional($templateId, $sender, $customerData->getEmail(), $customerData->getFirstname()." ".$customerData->getLastname(), $vars, $storeId);
				$updateQuery = "Update feedback_reminder set status='1' where shipment_id = '".$feedbackshipment['shipment_id']."'";
				$write->query($updateQuery);
			}
		}
	}
}
?>
