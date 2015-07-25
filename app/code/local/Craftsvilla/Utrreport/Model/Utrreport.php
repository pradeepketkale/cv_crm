<?php

class Craftsvilla_Utrreport_Model_Utrreport extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('utrreport/utrreport');
    }
}