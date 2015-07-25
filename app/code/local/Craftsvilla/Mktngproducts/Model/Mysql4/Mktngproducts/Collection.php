<?php

class Craftsvilla_Mktngproducts_Model_Mysql4_Mktngproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mktngproducts/mktngproducts');
    }
}
