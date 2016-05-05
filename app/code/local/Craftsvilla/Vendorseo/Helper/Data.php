<?php
class Craftsvilla_Vendorseo_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getVendorNameById($id) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "select vendor_name from vendor_info_craftsvilla where vendor_id =".$id. " LIMIT 1";
        $result = $read->query($query)->fetch();
        $read->closeConnection();
        return trim($result['vendor_name']);
       
    }
}
	 