<?php

class Craftsvilla_Productupdate_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('productupdate')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('productupdate')->__('Disabled')
        );
    }
}