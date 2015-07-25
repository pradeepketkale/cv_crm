<?php 
	class Craftsvilla_Coupons_Block_Coupon extends Mage_Core_Block_Template
	{
		public $today;
		public $customer_id;
		
		public function _construct(){
			$this->customer_id = Mage::getSingleton('customer/session')->getId();	
			$this->today = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		}
		
		
	}
?>