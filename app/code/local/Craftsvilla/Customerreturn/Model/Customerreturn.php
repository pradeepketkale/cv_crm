<?php

class Craftsvilla_Customerreturn_Model_Customerreturn extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreturn/customerreturn');
    }
}
