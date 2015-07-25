<?php

class Craftsvilla_Noticeboard_Model_Status extends Varien_Object
{
     const STATUS_APPROVED = 1;
	 const STATUS_NOT_APPROVED = 2;
	 
    static public function getOptionArray()
    {
        return array(
         	self::STATUS_APPROVED => Mage::helper('noticeboard')->__('Approved'),
			self::STATUS_NOT_APPROVED => Mage::helper('noticeboard')->__('Not Approved')
        );
    }
}