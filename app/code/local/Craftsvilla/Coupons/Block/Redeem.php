<?php

class Craftsvilla_Coupons_Block_Coupons extends Mage_Core_Block_Template
{
  // necessary methods
  public function __construct()
  {
	parent::__construct();
 	
	$show_coupons = new Craftsvilla_Coupons_Model_Coupons;
	$this->row = $show_coupons->getCoupons();
  }	
 
}
