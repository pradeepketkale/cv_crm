<?php

class Craftsvilla_Seocontent_Model_Mysql4_Seocontent_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('seocontent/seocontent');
    }
}