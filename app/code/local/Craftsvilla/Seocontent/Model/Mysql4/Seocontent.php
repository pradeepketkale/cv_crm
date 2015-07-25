<?php

class Craftsvilla_Seocontent_Model_Mysql4_Seocontent extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the seocontent_id refers to the key field in your database table.
        $this->_init('seocontent/seocontent', 'seocontent_id');
    }
}