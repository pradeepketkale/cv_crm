<?php
class Craftsvilla_Coupons_Model_Coupons extends Varien_Object
{
   public function getCoupons()
   {
		return $this->show_coupons();
   }
   
   public function show_coupons()
   {
		$customersessionData = $this->getCustomerSession();
		$customer_id = $customersessionData['id'];
		return $this->selectAllRecords($customer_id);
	}
   
   public function getCustomerSession()
   {
   		return Mage::getSingleton('customer/session');
   }
   
   public function selectAllRecords($customer_id)
   {
	    
	   	$query = "SELECT coupon_id FROM referfriend_referral WHERE referral_parent_id = $customer_id";
		$datacoupon = Mage::getSingleton('core/resource')->getConnection('catalog_read')->fetchAll($query);
		$CouponId = $datacoupon[0]['coupon_id'];
   		$resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('catalog_read');
		$salesrulecouponTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_coupon';
		$salesrulecustomerTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_customer';
		$salesruleTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule';
		$salesrulegiftedTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_gifted_status';
		//$select_files = "SELECT distinct(src.code) as code, sr.rule_id, sr.name, sr.discount_amount,src.times_used,  sc.customer_id, rf.coupon_id,src.expiration_date FROM salesrule sr INNER JOIN salesrule_coupon src ON sr.rule_id = src.rule_id INNER JOIN salesrule_customer sc ON sc.rule_id = src.rule_id INNER JOIN referfriend_referral rf ON rf.coupon_id = src.coupon_id WHERE sc.customer_id =  $customer_id";
		//new query generated by amit p
		$select_files="SELECT src.code as code, sr.rule_id, sr.name, sr.discount_amount, src.times_used, rf.coupon_id,src.expiration_date FROM salesrule_coupon src  LEFT JOIN salesrule sr ON src.rule_id = sr.rule_id LEFT JOIN referfriend_referral rf ON src.coupon_id = rf.coupon_id WHERE rf.referral_parent_id = $customer_id";
		return $read->fetchAll($select_files);
	}
}