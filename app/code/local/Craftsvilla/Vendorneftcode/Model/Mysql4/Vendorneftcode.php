<?php

class Craftsvilla_Vendorneftcode_Model_Mysql4_Vendorneftcode extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('vendorneftcode/vendorneftcode','vendorinfo_id');
    }
}
