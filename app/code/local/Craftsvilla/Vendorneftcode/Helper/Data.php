<?php

class Craftsvilla_Vendorneftcode_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCommissionPercentage($vendorId){

		$read = Mage::getSingleton('core/resource')->getConnection('custom_db');
		$sqlCommissionQuery = "Select `commission_percentage` from vendor_info_craftsvilla where vendor_id = '".$vendorId."'";
		$result = $read->query($sqlCommissionQuery)->fetch();
		return $result['commission_percentage'];
	}

}
