<?php

class Craftsvilla_Disputeraised_Model_Disputeraised extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('disputeraised/disputeraised');
    }
}