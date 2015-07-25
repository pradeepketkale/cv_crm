<?php
class Craftsvilla_Coupons_Block_Coupons extends Mage_Core_Block_Template
{
	public function __construct()
	{
		parent::__construct();
	}	
	public function getCouponData() {
		$show_coupons = new Craftsvilla_Coupons_Model_Coupons;
		
		return $show_coupons->getCoupons();
	}
}
