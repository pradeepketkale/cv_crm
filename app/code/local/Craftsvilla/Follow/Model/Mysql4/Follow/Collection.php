<?php

class Craftsvilla_Follow_Model_Mysql4_Follow_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('follow/follow');
    }
}