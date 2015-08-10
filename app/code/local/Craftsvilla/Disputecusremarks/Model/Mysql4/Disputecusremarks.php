<?php

class Craftsvilla_Disputecusremarks_Model_Mysql4_Disputecusremarks extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the disputecusremarks_id refers to the key field in your database table.
        $this->_init('disputecusremarks/disputecusremarks', 'disputecusremarks_id');
    }
}
