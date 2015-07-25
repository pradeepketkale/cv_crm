<?php

class Craftsvilla_Codshipments_Model_Mysql4_Codshipments extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('codshipments/codshipments', 'codshipments_id');
    }
}