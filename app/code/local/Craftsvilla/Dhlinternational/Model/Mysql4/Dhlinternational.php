<?php

class Craftsvilla_Dhlinternational_Model_Mysql4_Dhlinternational extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('dhlinternational/dhlinternational', 'id');
    }
}