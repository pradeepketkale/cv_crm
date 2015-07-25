<?php

class Craftsvilla_Follow_Model_Follow extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('follow/follow');
    }
}