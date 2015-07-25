<?php

class Craftsvilla_Activeshipment_Model_Activeshipment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('activeshipment/activeshipment');
    }
}