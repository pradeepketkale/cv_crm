<?php

class Craftsvilla_Homepage_Model_Mysql4_Homepage extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the homepage_id refers to the key field in your database table.
        $this->_init('homepage/homepage', 'homepage_id');
    }
}