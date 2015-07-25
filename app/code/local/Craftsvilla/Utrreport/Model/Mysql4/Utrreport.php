<?php

class Craftsvilla_Utrreport_Model_Mysql4_Utrreport extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the utrreport_id refers to the key field in your database table.
        $this->_init('utrreport/utrreport', 'utrreport_id');
    }
}