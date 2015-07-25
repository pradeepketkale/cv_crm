<?php

class Craftsvilla_Agentpayout_Model_Mysql4_Agentpayout_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('agentpayout/agentpayout');
    }
}