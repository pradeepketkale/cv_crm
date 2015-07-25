<?php

class Craftsvilla_Noticeboard_Model_Noticeboard extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('noticeboard/noticeboard');
    }
}