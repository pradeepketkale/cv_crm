<?php

class Craftsvilla_Productdownloadreq_Model_Activity extends Varien_Object
{
    const ACTIVITY_FULL_PRODUCT_DOWNLOAD	= 1;
    const ACTIVITY_INVENTORY_DOWNLOAD	= 2;
	

    static public function getOptionArray()
    {
        return array(
            self::ACTIVITY_FULL_PRODUCT_DOWNLOAD    => Mage::helper('productdownloadreq')->__('Full Product Download'),
			self::ACTIVITY_INVENTORY_DOWNLOAD    => Mage::helper('productdownloadreq')->__('Inventory Download')
			
        );
    }
}