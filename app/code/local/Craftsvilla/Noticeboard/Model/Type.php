<?php

class Craftsvilla_Noticeboard_Model_Type extends Varien_Object
{
     const TYPE_ADMIN = 3;
	 const TYPE_SELLER = 4;
    static public function getOptionArray()
    {
        return array(
         	self::TYPE_ADMIN => Mage::helper('noticeboard')->__('Admin'),
			self::TYPE_SELLER => Mage::helper('noticeboard')->__('Seller')
        );
    }
}