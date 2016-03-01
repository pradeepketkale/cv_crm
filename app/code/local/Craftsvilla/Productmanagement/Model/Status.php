<?php

class Craftsvilla_Productmanagement_Model_Status extends Varien_Object
{
    const STATUS_UPDATED	= 1;
    const STATUS_NOTUPDATED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_UPDATED    => Mage::helper('productmanagement')->__('Updated'),
            self::STATUS_NOTUPDATED   => Mage::helper('productmanagement')->__('Not Updated')
        );
    }
}
