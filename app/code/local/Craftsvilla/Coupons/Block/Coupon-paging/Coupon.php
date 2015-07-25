<?php

	class Craftsvilla_Coupons_Block_Coupon_Coupon extends Mage_Core_Block_Template
	{
		public $registration_status;
		public $purchase_status;
		

		public function _construct(){
			$this->setTemplate('coupons/coupon/coupon.phtml');	
			
			//$orders = Mage::getResourceModel('coupons/coupon_collection')
				//->addFieldToFilter('coupon_parent_id', Mage::getSingleton('customer/session')->getId())
				//->addFieldToFilter('coupon_register_status', '1')
				//->addFieldToSelect('*');
			// custom query 
/*
			$customersessionData =  Mage::getSingleton('customer/session');
		$customer_id = $customersessionData['id'];

			$salesrulecouponTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_coupon';
		$salesrulecustomerTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_customer';
		$salesruleTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule';
		$salesrulegiftedTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_gifted_status';
		$salescoupontypeTable = (string)Mage::getConfig()->getTablePrefix() . 'salesrule_coupon_type';

			$collection = Mage::getModel('coupons/coupon')->getCollection(); 
			$collection -> getSelect() ->from(array('sr' => $salesruleTable), array('coupon_type' => 'sct.coupon_type', 'sent_date' => 'sgs.sent_date', 'friend_email_address' => 'sgs.friend_email_address', 'status_id' => 'sgs.status_id','rules_id' => 'sr.rule_id','name' => 'src.code', 'description' => 'sr.description', 'times' => 'src.times_used', 'discount_amount' => 'sr.discount_amount'))->join(array('src' => $salesrulecouponTable), 'sr.rule_id = src.rule_id')->join(array('srco' => $salesrulecustomerTable), 'sr.rule_id = srco.rule_id')->join(array('sct' => $salescoupontypeTable), 'sr.rule_id = sct.rule_id')->joinLeft(array('sgs' => $salesrulegiftedTable), 'sr.rule_id = sgs.rule_id')->where("srco.customer_id  = ?",$customer_id)->where("src.times_used = ?",'0');
			// custom query end
	
			$this->setCoupons($collection);
	*/
			Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
			
		}
		/*
		protected function _prepareLayout()
		{
			
			parent::_prepareLayout();			
				
			$pager = $this->getLayout()->createBlock('page/html_pager', 'coupons.coupon.coupon.pager')
				->setCollection($this->getCoupons());
			$this->setChild('pager', $pager);
			$this->getCoupons()->load();
			return $this;
		}
		
		
		
		
		public function getPagerHtml()
		{
			return $this->getChildHtml('pager');
		}*/
	}