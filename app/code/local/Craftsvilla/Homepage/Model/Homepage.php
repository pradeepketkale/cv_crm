<?php

class Craftsvilla_Homepage_Model_Homepage extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('homepage/homepage');
    }
}