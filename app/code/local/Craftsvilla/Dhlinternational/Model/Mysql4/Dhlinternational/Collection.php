<?php

class Craftsvilla_Dhlinternational_Model_Mysql4_Dhlinternational_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dhlinternational/dhlinternational');
    }
}