<?php

class Craftsvilla_Customerreturn_Model_Mysql4_Customerreturn extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('customerreturn/customerreturn', 'customerreturn_id');
    }
}
