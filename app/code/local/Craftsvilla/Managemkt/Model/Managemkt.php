<?php

class Craftsvilla_Managemkt_Model_Managemkt extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('managemkt/managemkt');
    }
}