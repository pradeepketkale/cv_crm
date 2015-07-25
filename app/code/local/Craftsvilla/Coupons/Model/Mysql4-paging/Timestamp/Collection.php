<?php

class Craftsvilla_Coupons_Model_Mysql4_Timestamp_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('coupons/timestamp');
    }
}
