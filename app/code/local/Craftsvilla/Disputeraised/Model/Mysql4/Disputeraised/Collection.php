<?php

class Craftsvilla_Disputeraised_Model_Mysql4_Disputeraised_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('disputeraised/disputeraised');
    }
}