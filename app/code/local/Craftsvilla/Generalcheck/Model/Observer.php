<?php
class Craftsvilla_Generalcheck_Model_Observer
{


public function crmTogetProductEventafter($observer) 
	{
		$pId = $observer->getEvent()->getProduct()->getId();
		$productId=str_getcsv($pId,',','""');
		$hlp = Mage::helper('generalcheck');
		$hlp->productUpdateNotify_retry($productId);
	}  

public function crmTogetOrderCancelEventafter($obs){
		//echo "hello";exit;		
		$shipment = $obs->getEvent()->getShipment();
		$shipmentId = $shipment->getEntityId();

		 $_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
		 $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;//exit;
		$pid=array();
		if($_shipmentUdropshipstatus == $statusCanceled){
			    $items = $shipment->getAllItems();
		foreach ($items as $item) {
                    $productsToUpdate = $item->getProductId();
			array_push($pid,$productsToUpdate);
	            		 }

			   $hlp = Mage::helper('generalcheck');
	     		   $hlp->productUpdateNotify_retry($pid);
		

			}
		


	}       

}
?>
