<?php

class Craftsvilla_Ebslink_Model_Mysql4_Ebslink extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the ebslink_id refers to the key field in your database table.
        $this->_init('ebslink/ebslink', 'ebslink_id');
    }
}