<?php

class Craftsvilla_Mktngproducts_Model_Status extends Varien_Object
{
    const STATUS_UPDATED	= 1;
    const STATUS_NOTUPDATED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_UPDATED    => Mage::helper('mktngproducts')->__('Updated'),
            self::STATUS_NOTUPDATED   => Mage::helper('mktngproducts')->__('Not Updated')
        );
    }
}
