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



}
?>
