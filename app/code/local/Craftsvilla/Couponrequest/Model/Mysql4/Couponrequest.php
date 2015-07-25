<?php

class Craftsvilla_Couponrequest_Model_Mysql4_Couponrequest extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the couponrequest_id refers to the key field in your database table.
        $this->_init('couponrequest/couponrequest', 'couponrequest_id');
    }
}
