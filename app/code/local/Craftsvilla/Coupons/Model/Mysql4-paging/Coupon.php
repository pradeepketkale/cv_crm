<?php

class Craftsvilla_Coupons_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('coupons/coupon', 'coupon_id');
    }
}
