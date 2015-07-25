<?php

class Craftsvilla_Follow_Model_Mysql4_Follow extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the follow_id refers to the key field in your database table.
        $this->_init('follow/follow', 'follow_id');
    }
}