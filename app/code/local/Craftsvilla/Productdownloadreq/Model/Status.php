<?php

class Craftsvilla_Productdownloadreq_Model_Status extends Varien_Object
{
    const STATUS_REQUESTED	= 1;
    const STATUS_COMPLETED	= 2;
	

    static public function getOptionArray()
    {
        return array(
            self::STATUS_REQUESTED    => Mage::helper('productdownloadreq')->__('Requested'),
			self::STATUS_COMPLETED    => Mage::helper('productdownloadreq')->__('Completed')
			
        );
    }
}