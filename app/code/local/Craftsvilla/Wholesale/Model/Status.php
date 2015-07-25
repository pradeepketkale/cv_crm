<?php

class Craftsvilla_Wholesale_Model_Status extends Varien_Object
{
    const STATUS_OPEN	= 1;
    const STATUS_QUALIFIED = 2;
	const STATUS_PROCESSING = 3;
	const STATUS_PAYMENT_RECEIVED = 4;
	const STATUS_DELIVERED = 5;
	const STATUS_CLOSED = 6;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_OPEN    => Mage::helper('wholesale')->__('Open'),
            self::STATUS_QUALIFIED   => Mage::helper('wholesale')->__('Qualified'),
        	self::STATUS_PROCESSING	 => Mage::helper('wholesale')->__('Processing'),
			self::STATUS_PAYMENT_RECEIVED	=> Mage::helper('wholesale')->__('Payment Received'),
			self::STATUS_DELIVERED	=> 	Mage::helper('wholesale')->__('Delivered'),
			self::STATUS_CLOSED	=> 	Mage::helper('wholesale')->__('Closed'),
		);
    }
}
