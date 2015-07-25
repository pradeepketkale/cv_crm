<?php

class Craftsvilla_Seocontent_Model_Seocontent extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('seocontent/seocontent');
    }
}