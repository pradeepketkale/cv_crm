<?php

class Craftsvilla_Couponrequest_Model_Couponrequest extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('couponrequest/couponrequest');
    }
}
