<?php

class Craftsvilla_Coupons_Model_Mysql4_Timestamp extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('coupons/timestamp', 'time_stamp_id');
    }
}
