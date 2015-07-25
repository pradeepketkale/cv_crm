<?php

class Craftsvilla_Customerreturn_Model_Mysql4_Customerreturn_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreturn/customerreturn');
    }
}
