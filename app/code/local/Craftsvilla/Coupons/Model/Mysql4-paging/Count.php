<?php

class Craftsvilla_Coupons_Model_Mysql4_Count extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('coupons/count', 'count_id');
    }
}
