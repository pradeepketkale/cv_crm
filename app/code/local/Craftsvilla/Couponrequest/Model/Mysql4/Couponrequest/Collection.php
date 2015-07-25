<?php

class Craftsvilla_Couponrequest_Model_Mysql4_Couponrequest_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('couponrequest/couponrequest');
    }
}
