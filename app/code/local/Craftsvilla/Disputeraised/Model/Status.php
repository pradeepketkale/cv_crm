<?php

class Craftsvilla_Disputeraised_Model_Status extends Varien_Object
{
    const STATUS_OPEN	= 1;
    const STATUS_CLOSED 	= 2;
    const STATUS_WAITING_FOR_SELLER_RESPONSE 	= 3;
     const STATUS_WAITING_FOR_CUSTOMER_RESPONSE 	= 4;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_OPEN    => Mage::helper('disputeraised')->__('Open'),
            self::STATUS_CLOSED   => Mage::helper('disputeraised')->__('Closed'),
            self::STATUS_WAITING_FOR_SELLER_RESPONSE   => Mage::helper('disputeraised')->__('Waiting For Seller Response'),
            self::STATUS_WAITING_FOR_CUSTOMER_RESPONSE   => Mage::helper('disputeraised')->__('Waiting For Customer Response')
        );
    }
}
