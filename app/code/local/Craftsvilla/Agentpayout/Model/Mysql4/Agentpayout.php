<?php

class Craftsvilla_Agentpayout_Model_Mysql4_Agentpayout extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the Agentpayout_id refers to the key field in your database table.
        $this->_init('agentpayout/agentpayout', 'agentpayout_id');
    }
}