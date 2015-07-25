<?php

class Craftsvilla_Coupons_Model_Mysql4_Check extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('coupons/check', 'check_id');
    }
	
	
}
