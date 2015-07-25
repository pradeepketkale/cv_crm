<?php

class Craftsvilla_Customerreturn_Model_Status extends Varien_Object
{
    const STATUS_INTRANSIT	= 1;
    const STATUS_DELIVEREDTOSELLER	= 2;
    const STATUS_NEEDBANKDETAILS = 3;
	const STATUS_INCORRECTTRACKING = 4;	

    static public function getOptionArray()
    {
        return array(
            self::STATUS_INTRANSIT   => Mage::helper('customerreturn')->__('In Transit'),
			self::STATUS_DELIVEREDTOSELLER    => Mage::helper('customerreturn')->__('Delivered To Seller'),
            self::STATUS_NEEDBANKDETAILS   => Mage::helper('customerreturn')->__('Need Bank Details'),
			self::STATUS_INCORRECTTRACKING   => Mage::helper('customerreturn')->__('Incorrect Tracking')
        );
    }
}
