<?php

class Craftsvilla_Refundrequestedcustomer_Model_Status extends Varien_Object
{
    const STATUS_REQUESTED	= 1;
    const STATUS_APPROVED	= 2;
	const STATUS_REJECTED	= 3;
	const STATUS_CHECKED	= 4;
	
    static public function getOptionArray()
    {
        return array(
            self::STATUS_REQUESTED    => Mage::helper('refundrequestedcustomer')->__('Requested'),
            self::STATUS_APPROVED    => Mage::helper('refundrequestedcustomer')->__('Approved'),
            self::STATUS_REJECTED   => Mage::helper('refundrequestedcustomer')->__('Rejected'),
            self::STATUS_CHECKED   => Mage::helper('refundrequestedcustomer')->__('Checked')
        );
    }
}
