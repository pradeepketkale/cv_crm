<?php

class Craftsvilla_Mktvendors_Model_Mktvendors extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mktvendors/mktvendors');
    }
}