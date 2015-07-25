<?php

class Craftsvilla_Managemkt_Model_Status extends Varien_Object
{
    const STATUS_REQUESTED	= 1;
    const STATUS_ACCEPTED	= 2;
	const STATUS_EXECUTED	= 3;
	const STATUS_ONHOLD		= 4;
	const STATUS_DECLINED   = 5;
	const STATUS_CANCELLED  = 6;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_REQUESTED    => Mage::helper('managemkt')->__('Requested'),
			self::STATUS_ACCEPTED    => Mage::helper('managemkt')->__('Accepted'),
			self::STATUS_EXECUTED    => Mage::helper('managemkt')->__('Executed'),
			self::STATUS_ONHOLD    => Mage::helper('managemkt')->__('On Hold'),
			self::STATUS_DECLINED    => Mage::helper('managemkt')->__('Declined'),
            self::STATUS_CANCELLED   => Mage::helper('managemkt')->__('Cancelled')
        );
    }
}