<?php

class Craftsvilla_Productmanagement_Model_Productmanagement extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productmanagement/productmanagement');
    }
}
