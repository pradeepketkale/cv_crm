<?php
class Craftsvilla_Customer_Model_Customer_Api extends Mage_Customer_Model_Customer_Api
{
	public function utmCreate($customerData,$campaignData)
	{
		$customerData = $this->_prepareData($customerData);
		try {
			//$customer = Mage::getModel('customer/customer');
			//$customer_id = $customer->setWebsiteId(array(0,1))->loadByEmail($customerData['email'])->getId();
			$query = "SELECT entity_id from customer_entity where email = '".$customerData['email']."'";
			$data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
			if(!empty($data)) {
				return "Customer already exists.";
			}
			else {
				$customer->setData($customerData)
					->save();
				$w = Mage::getSingleton('core/resource')->getConnection('core_write');
				$utmsource        = $campaignData['utmsource'];
				$utmmedium        = $campaignData['utmmedium'];
				$utmcampaign      = $campaignData['utmcampaign'];
				$last_customer_id = $customer->getId();
		
				$insCookieData = array('entity_id'=>$last_customer_id, 'utm_source'=>$utmsource, 'utm_campaign'=>$utmcampaign,'utm_medium'=>$utmmedium);

				$w->insert('customer_utm_master', $insCookieData);
			}
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return $customer->getId();
	}
}
?>
