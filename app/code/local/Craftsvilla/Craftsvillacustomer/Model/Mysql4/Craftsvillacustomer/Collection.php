<?php

class Craftsvilla_Craftsvillacustomer_Model_Mysql4_Craftsvillacustomer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('craftsvillacustomer/craftsvillacustomer');
    }
}