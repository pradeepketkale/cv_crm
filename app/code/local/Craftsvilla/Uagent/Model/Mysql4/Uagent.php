<?php

class Craftsvilla_Uagent_Model_Mysql4_Uagent extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('uagent/uagent', 'agent_id');
    }
}