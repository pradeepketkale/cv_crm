<?php

class Craftsvilla_Codshipments_Model_Mysql4_Codshipments_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('codshipments/codshipments');
    }
}