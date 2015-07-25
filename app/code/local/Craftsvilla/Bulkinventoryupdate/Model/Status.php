<?php

class Craftsvilla_Bulkinventoryupdate_Model_Status extends Varien_Object
{
    const STATUS_PROCESSING	= 1;
    const STATUS_COMPLETED	= 2;
	const STATUS_SUBMITTED	= 3;
	const STATUS_REJECTED	= 4;
    const STATUS_APPROVED = 5;
    static public function getOptionArray()
    {
        return array(
            self::STATUS_PROCESSING    => Mage::helper('bulkinventoryupdate')->__('Processing'),
            self::STATUS_COMPLETED   => Mage::helper('bulkinventoryupdate')->__('Completed'),
			self::STATUS_SUBMITTED   => Mage::helper('bulkinventoryupdate')->__('Submitted'),
			self::STATUS_REJECTED   => Mage::helper('bulkinventoryupdate')->__('Rejected'),
			self::STATUS_APPROVED => Mage::helper('bulkuploadcsv')->__('Approved')
        );
    }
}