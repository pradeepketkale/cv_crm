<?php
class Marketplace_Feedback_Model_Observer
{
	public function commentSave($observer)
	{
		$shipment = $observer->getEvent()->getShipment();
		$order = $observer->getEvent()->getShipment()->getOrder();
		if($shipment->getUdropshipStatus()==1){
			$read = Mage::getSingleton('core/resource')->getConnection('feedback_read');
			//////////////////// Suresh Shipment Payout Code ///////////////////////////////////////////////////////////////
				$sql= "select shipmentpayout_id from shipmentpayout where shipment_id = '".$shipment->getIncrementId()."'";
				$shipmentpayoutData = $read->fetchAll($sql);
				if(!$shipmentpayoutData){
					$created_time = date("Y-m-d h:i:s");
					$shipmentpayout = Mage::getSingleton('shipmentpayout/shipmentpayout')
										->setShipmentId($shipment->getIncrementId())
										->setOrderId($order->getIncrementId())
										->setShipmentpayoutCreatedTime($created_time)
										->save();        
				}
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			/////////////////// Amit Pitre Code For Feedback Reminder //////////////////////////////////////////////////////
				$sql= "select entity_id from feedback_reminder where shipment_id = '".$shipment->getIncrementId()."'";
				$feedbackshipmentData = $read->fetchAll($sql);
				if(!$feedbackshipmentData){
					$write = Mage::getSingleton('core/resource')->getConnection('feedback_write');
					$follow_reminder_date = date('Y-m-d',strtotime("+7 days"));
					$insertQuery = "insert into feedback_reminder set shipment_id = '".$shipment->getId()."' , follow_reminder_date = '".$follow_reminder_date."'";
					$write->query($insertQuery);
					
				}			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		}
	}
}
