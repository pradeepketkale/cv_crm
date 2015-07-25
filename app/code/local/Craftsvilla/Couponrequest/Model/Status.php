<?php

class Craftsvilla_Couponrequest_Model_Status extends Varien_Object
{
    const STATUS_REQUESTED	= 1;
    const STATUS_APPROVED	= 2;
    const STATUS_REJECTED   = 3;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_REQUESTED    => Mage::helper('couponrequest')->__('Requested'),
            self::STATUS_APPROVED   => Mage::helper('couponrequest')->__('Approved'),
            self::STATUS_REJECTED   => Mage::helper('couponrequest')->__('Rejected')
        );
    }
}
