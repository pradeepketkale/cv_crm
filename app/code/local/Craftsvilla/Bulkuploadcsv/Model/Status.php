<?php

class Craftsvilla_Bulkuploadcsv_Model_Status extends Varien_Object
{
    const STATUS_PROCESSING	         = 1;
    const STATUS_COMPLETED	         = 2;
	const STATUS_SUBMITTED	         = 3;
	const STATUS_REJECTED	         = 4;
    const STATUS_APPROVED            = 5;
     const STATUS_APPROVED_VARIANTS  = 6;
    static public function getOptionArray()
    {
        return array(
            self::STATUS_PROCESSING    => Mage::helper('bulkuploadcsv')->__('Processing'),
            self::STATUS_COMPLETED   => Mage::helper('bulkuploadcsv')->__('Completed'),
			self::STATUS_SUBMITTED   => Mage::helper('bulkuploadcsv')->__('Submitted'),
			self::STATUS_REJECTED   => Mage::helper('bulkuploadcsv')->__('Rejected'),
			self::STATUS_APPROVED => Mage::helper('bulkuploadcsv')->__('Approved'),
            self::STATUS_APPROVED => Mage::helper('bulkuploadcsv')->__('Approved For Variants')
        );
    }
}