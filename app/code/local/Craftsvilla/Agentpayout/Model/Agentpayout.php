<?php

class Craftsvilla_Agentpayout_Model_Agentpayout extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('agentpayout/agentpayout');
    }
}