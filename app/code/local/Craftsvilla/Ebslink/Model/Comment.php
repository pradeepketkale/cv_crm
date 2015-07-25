<?php

class Craftsvilla_Ebslink_Model_Comment extends Varien_Object
{
    const COMMENT_COD_PAYMENT_NEEDED = 1;
    const COMMENT_INTERESTED_WILL_PAY = 2;
	const COMMENT_NOT_INTEREDTED_CANCEL = 3;
	const COMMENT_BUSY_CALL_AGAIN = 4;
	const COMMENT_NOT_REACHABLE = 5;
	const COMMENT_INTERNATIONAL_CALL = 6;
	const COMMENT_OTHER = 7;

    static public function getOptionArray()
    {
        return array(
            self::COMMENT_COD_PAYMENT_NEEDED    => Mage::helper('ebslink')->__('COD Payment Needed'),
            self::COMMENT_INTERESTED_WILL_PAY   => Mage::helper('ebslink')->__('Interested Will Pay'),
			self::COMMENT_NOT_INTEREDTED_CANCEL   => Mage::helper('ebslink')->__('Not Interested Cancel'),
			self::COMMENT_BUSY_CALL_AGAIN   => Mage::helper('ebslink')->__('Busy Call Again'),
			self::COMMENT_NOT_REACHABLE   => Mage::helper('ebslink')->__('Not Reachable'),
			self::COMMENT_INTERNATIONAL_CALL   => Mage::helper('ebslink')->__('International Call'),
			self::COMMENT_OTHER   => Mage::helper('ebslink')->__('Other')
        );
    }
}