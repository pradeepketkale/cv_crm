<?php

class Craftsvilla_Craftsvillapickupreference_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('craftsvillapickupreference')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('craftsvillapickupreference')->__('Disabled')
        );
    }
}
