<?php

class Craftsvilla_Noticeboard_Model_Mysql4_Noticeboard_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('noticeboard/noticeboard');
    }
}