<?php

class Craftsvilla_Managemkt_Model_Mysql4_Managemkt_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('managemkt/managemkt');
    }
}