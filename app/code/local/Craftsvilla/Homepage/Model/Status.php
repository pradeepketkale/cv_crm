<?php

class Craftsvilla_Homepage_Model_Status extends Varien_Object
{
    const STATUS_ASSIGNED	= 1;
    const STATUS_NOT_ASSIGNED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ASSIGNED    => Mage::helper('homepage')->__('Assigned'),
            self::STATUS_NOT_ASSIGNED   => Mage::helper('homepage')->__('Not Assigned')
        );
    }
}