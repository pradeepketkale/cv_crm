<?php

class Craftsvilla_Vendorneftcode_Model_Mysql4_Vendorneftcode_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendorneftcode/vendorneftcode');
    }
}
